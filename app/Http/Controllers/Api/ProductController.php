<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::where('user_id', $request->user()->id)->get();

        // Transform each product's images to full URLs
        $products->transform(function ($product) {
            if (is_array($product->images)) {
                $product->images = array_map(function ($path) {
                    return Storage::url($path);  // e.g. /storage/products/filename.jpg
                }, $product->images);
            }
            return $product;
        });

        return $products;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discountPrice' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:available,out_of_stock,discontinued',
            'images' => 'nullable',
            'images.*' => 'image|max:2048',  // validate each uploaded file as image max 2MB
        ]);

        $validated['user_id'] = $request->user()->id;
        $product = Product::create($validated);

        // Handle image uploads if any
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $imagePaths[] = $path;
            }
            $product->images = $imagePaths;
            $product->save();
        }

        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $products = Product::findOrFail($id);

        $products->transform(function ($product) {
            if (is_array($product->images)) {
                $product->images = array_map(fn($path) => Storage::url($path), $product->images);
            }
            return $product;
        });

        return response()->json($products);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discountPrice' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:available,out_of_stock,discontinued',
            'images' => 'nullable',
            'images.*' => 'image|max:2048',
        ]);

        $product = Product::findOrFail($id);
        if ($product->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $product->update($validated);

        // Handle image uploads if any
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $imagePaths[] = $path;
            }
            $product->images = $imagePaths;
            $product->save();
        }

        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     */
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
