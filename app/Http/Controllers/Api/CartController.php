<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\FacadesAuth;
use Illuminate\Support\Facades\Storage;


class CartController extends Controller
{
    public function index()
    {
        $cartItems = auth()->user()->cartItems()->with('product')->get();

        return response()->json($cartItems);
    }
    /**
     * Add a product to the cart.
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'variations' => 'nullable|array',
        ]);

        $user = auth()->user();
        $productId = $request->product_id;
        $newQuantity = $request->quantity;
        $newVariations = $request->variations ? json_encode($request->variations) : null;

        // Get all cart items for the user and product
        $cartItems = $user->cartItems()->where('product_id', $productId)->get();

        $existingCartItem = null;

        // Iterate through existing cart items to find a match with variations
        foreach ($cartItems as $item) {
            $existingVariations = $item->variations;

            if ($newVariations === $existingVariations) {
                $existingCartItem = $item;
                break;
            }

            // Decode JSON and compare arrays for more robust variation comparison
            $existingVariationsArray = json_decode($existingVariations, true);
            $newVariationsArray = json_decode($newVariations, true);

            if ($existingVariationsArray !== null && $newVariationsArray !== null && $existingVariationsArray === $newVariationsArray) {
                 $existingCartItem = $item;
                 break;
            }
        }


        if ($existingCartItem) {
            // Update quantity by adding new quantity
            $existingCartItem->quantity += $newQuantity;
            $existingCartItem->save();

            return response()->json($existingCartItem, 200);
        } else {
            // Create new cart item
            $cartItem = $user->cartItems()->create([
                'product_id' => $productId,
                'quantity' => $newQuantity,
                'variations' => $newVariations,
            ]);

            return response()->json($cartItem, 201);
        }
    }

    public function update(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Find the cart item
        $cartItem = auth()->user()->cartItems()->findOrFail($id);

        // Update the quantity
        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json($cartItem);
    }

    public function remove($id)
    {
        // Find the cart item
        $cartItem = auth()->user()->cartItems()->findOrFail($id);

        // Delete the cart item
        $cartItem->delete();

        return response()->json(['message' => 'Cart item removed successfully.']);
    }

    public function clear()
    {
        // Clear the cart for the authenticated user
        auth()->user()->cartItems()->delete();

        return response()->json(['message' => 'Cart cleared successfully.']);
    }

    public function show($id)
    {
        // Find the cart item
        $cartItem = auth()->user()->cartItems()->findOrFail($id);

        return response()->json($cartItem);
    }

    public function getCartSummary()
    {
        $cartItems = auth()->user()->cartItems()->with('product')->get();

        $totalQuantity = $cartItems->sum('quantity');
        $totalPrice = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        return response()->json([
            'total_quantity' => $totalQuantity,
            'total_price' => $totalPrice,
            'items' => $cartItems,
        ]);
    }

}
