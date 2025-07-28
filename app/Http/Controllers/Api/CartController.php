<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
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

        // Search for existing cart item with same product and variations
        $existingCartItemQuery = $user->cartItems()
            ->where('product_id', $request->product_id);

        if ($request->has('variations')) {
            // Assuming you store variations as JSON in DB
            $existingCartItemQuery->where('variations', json_encode($request->variations));
        } else {
            // If no variations in request, look for cart items with NULL or empty variations
            $existingCartItemQuery->whereNull('variations');
        }

        $existingCartItem = $existingCartItemQuery->first();

        if ($existingCartItem) {
            // Update quantity by adding new quantity
            $existingCartItem->quantity += $request->quantity;
            $existingCartItem->save();

            return response()->json($existingCartItem, 200);
        } else {
            // Create new cart item
            $cartItem = $user->cartItems()->create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'variations' => $request->variations ? json_encode($request->variations) : null,
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

}
