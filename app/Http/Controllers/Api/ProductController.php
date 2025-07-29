<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('variants')->where('user_id', $request->user()->id)->get();

        $products->transform(function ($product) {
            if (is_array($product->images)) {
                $product->images = array_map(fn($path) => Storage::url($path), $product->images);
            }
            return $product;
        });

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discountPrice' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:available,out_of_stock,discontinued',
            'images' => 'nullable',
            'images.*' => 'image|max:2048',
            // variants validation skipped, since it's JSON via FormData
        ]);

        $validated['user_id'] = $request->user()->id;
        $product = Product::create($validated);

        // Handle images
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('products', 'public');
            }
            $product->images = $imagePaths;
            $product->save();
        }

        // Handle variants
        if ($request->has('variants')) {
            $variants = json_decode($request->input('variants'), true);

            \Log::info('Variants received:', $variants);

            if (is_array($variants)) {
                foreach ($variants as $variantData) {
                    if (isset($variantData['variation_values'], $variantData['stock'])) {
                        $product->variants()->create([
                            'variation_values' => $variantData['variation_values'],
                            'price' => $variantData['price'] ?? null,
                            'stock' => $variantData['stock'],
                        ]);
                    } else {
                        \Log::warning('Variant data missing required fields', $variantData);
                    }
                }
            } else {
                \Log::warning('Variants is not an array', ['variants' => $variants]);
            }
        }

        return response()->json($product->load('variants'), 201);
    }

    public function show(string $id)
    {
        $product = Product::with('variants')->findOrFail($id);

        if (is_array($product->images)) {
            $product->images = array_map(fn($path) => Storage::url($path), $product->images);
        }

        return response()->json($product);
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        if ($product->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discountPrice' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:available,out_of_stock,discontinued',
            'images' => 'nullable',
            'images.*' => 'image|max:2048',
        ]);

        $product->update($validated);

        // Handle images
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('products', 'public');
            }
            $product->images = $imagePaths;
            $product->save();
        }

        return response()->json($product->load('variants'));
    }

    public function destroy(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        if ($product->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $product->delete();

        return response()->json(null, 204);
    }
}
