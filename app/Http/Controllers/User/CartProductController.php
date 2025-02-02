<?php

namespace App\Http\Controllers\User;
use App\Models\Cart;
use App\Models\CartProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\CartResource;
use App\Http\Requests\User\CartProductRequest;


class CartProductController extends Controller
{
    public function addToCart(CartProductRequest $request)
{
    if (auth()->guard('admin')->check()) {
        $cart = Cart::firstOrCreate([
            'user_id' => $request->user_id ?? null,
            'admin_id' => auth()->guard('admin')->id(),
            'status' => 'active',
        ]);
    } else {
        $cart = Cart::firstOrCreate([
            'user_id' => auth()->guard('api')->id(),
            'status' => 'active'
        ]);
    }

    foreach ($request->products as $product) {
        $cart->products()->syncWithoutDetaching([
            $product['product_id'] => [
                'quantity' => DB::raw('quantity + ' . $product['quantity'])
            ]
        ]);
    }

    return response()->json([
        'message' => 'تمت إضافة المنتجات إلى السلة بنجاح',
        'cart' => new CartResource($cart->load('products.category', 'user', 'admin')) // تضمين admin في الـ load
    ]);
}


public function showCart($id)
{

    $currentUser = auth()->guard('api')->user();
    if (!$currentUser) {
        $currentUser = auth()->guard('admin')->user();
    }

    if (!$currentUser) {
        return response()->json([
            'message' => 'Unauthorized User'
        ], 401);
    }

    if ($currentUser->role_id == 1) {
        $cart = Cart::find($id);
    }
    else {
        $cart = Cart::where('id', $id)
                    ->where('user_id', $currentUser->id)
                    ->where('status', 'active')
                    ->first();
    }

    if (!$cart) {
        return response()->json([
            'message' => 'Cart not found or unauthorized access'
        ], 404);
    }

    return new CartResource($cart);
}

public function updateCartItem(CartProductRequest $request, $id)
{
    $currentUser = auth()->guard('api')->user();
    if (!$currentUser) {
        $currentUser = auth()->guard('admin')->user();
    }

    if (!$currentUser) {
        return response()->json([
            'message' => 'Unauthorized User'
        ], 401);
    }

    // السماح للأدمن بالوصول إلى أي سلة
    if ($currentUser->role_id == 1) {
        $cart = Cart::where('id', $id)->where('status', 'active')->first();
    } else {
        // السماح للمستخدم فقط بتعديل سلته
        $cart = Cart::where('id', $id)
                    ->where(function ($query) use ($currentUser) {
                        $query->where('user_id', $currentUser->id)
                              ->orWhere('admin_id', $currentUser->id);
                    })
                    ->where('status', 'active')
                    ->first();
    }

    if (!$cart) {
        return response()->json([
            'message' => 'Cart not found or unauthorized access'
        ], 404);
    }

    foreach ($request->products as $product) {
        // تحديث الكمية إذا كان المنتج موجودًا، وإضافته إذا لم يكن موجودًا
        $cart->products()->syncWithoutDetaching([
            $product['product_id'] => ['quantity' => $product['quantity']]
        ]);
    }

    return response()->json([
        'message' => 'تم تحديث كمية المنتجات في السلة بنجاح',
        'cart' => new CartResource($cart->load('products.category', 'user', 'admin'))
    ]);
}



public function removeCartItem($id)
{
    $currentUser = auth()->guard('api')->user();
    if (!$currentUser) {
        $currentUser = auth()->guard('admin')->user();
    }

    if (!$currentUser) {
        return response()->json([
            'message' => 'Unauthorized User'
        ], 401);
    }

    $cartProduct = CartProduct::findOrFail($id);

    // جلب بيانات السلة الخاصة بالمنتج
    $cart = Cart::where('id', $cartProduct->cart_id)
                ->where('status', 'active')
                ->first();

    if (!$cart) {
        return response()->json([
            'message' => 'Cart not found or unauthorized access'
        ], 404);
    }

    // السماح للأدمن بحذف أي منتج من أي سلة
    if ($currentUser->role_id != 1) {
        // السماح فقط لصاحب السلة بحذف المنتج
        if ($cart->user_id != $currentUser->id && $cart->admin_id != $currentUser->id) {
            return response()->json([
                'message' => 'Unauthorized to remove item from this cart'
            ], 403);
        }
    }

    $cartProduct->delete();

    return response()->json([
        'message' => 'تم حذف المنتج من السلة بنجاح',
        'cart' => new CartResource($cart->load('products.category', 'user', 'admin'))
    ]);
}


}
