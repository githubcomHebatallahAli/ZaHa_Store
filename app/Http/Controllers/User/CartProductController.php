<?php

namespace App\Http\Controllers\User;
use App\Models\Cart;
use App\Models\CartProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\CartResource;
use App\Http\Resources\User\ShowCartResource;
use App\Http\Requests\User\CartProductRequest;


class CartProductController extends Controller
{
//     public function addToCart(CartProductRequest $request)
// {
//     if (auth()->guard('admin')->check()) {
//         $cart = Cart::firstOrCreate([
//             'user_id' => $request->user_id ?? null,
//             'admin_id' => auth()->guard('admin')->id(),
//             'status' => 'active',
//         ]);
//     } else {
//         $cart = Cart::firstOrCreate([
//             'user_id' => auth()->guard('api')->id(),
//             'status' => 'active'
//         ]);
//     }

//     foreach ($request->products as $product) {
//         $cart->products()->syncWithoutDetaching([
//             $product['product_id'] => [
//                 'quantity' => DB::raw('quantity + ' . $product['quantity'])
//             ]
//         ]);
//     }

//     return response()->json([
//         'message' => 'تمت إضافة المنتجات إلى السلة بنجاح',
//         'cart' => new CartResource($cart->load('products.category', 'user', 'admin')) // تضمين admin في الـ load
//     ]);
// }


public function addToCart(CartProductRequest $request)
{
    // إنشاء أو استرجاع السلة
    if (auth()->guard('admin')->check()) {
        $cart = Cart::firstOrCreate([
            'user_id' => $request->user_id ?? null,
            'admin_id' => auth()->guard('admin')->id(),
            // 'status' => 'active',
        ]);
    } else {
        $cart = Cart::firstOrCreate([
            'user_id' => auth()->guard('api')->id(),
            // 'status' => 'active'
        ]);
    }

    // إضافة المنتج إلى السلة
    $cart->products()->syncWithoutDetaching([
        $request->product_id => [
            'quantity' => $request->quantity
        ]
    ]);

    // إرسال طلب بعد إضافة المنتج
    $response = $this->sendRequestAfterProductAdded($request->only(['product_id', 'quantity']));

    // إرجاع الرد
    return response()->json([
        'message' => 'تمت إضافة المنتج إلى السلة بنجاح',
        'cart' => new CartResource($cart->load('products.category', 'user', 'admin')),
        'response' => $response
    ]);
}

private function sendRequestAfterProductAdded($product)
{
    return [
        'product_id' => $product['product_id'],
        'quantity' => $product['quantity'],
        'message' => 'تم إرسال الطلب بنجاح بعد إضافة المنتج'
    ];
}


// public function showCart($id)
// {

//     $currentUser = auth()->guard('api')->user();
//     if (!$currentUser) {
//         $currentUser = auth()->guard('admin')->user();
//     }

//     if (!$currentUser) {
//         return response()->json([
//             'message' => 'Unauthorized User'
//         ], 401);
//     }

//     if ($currentUser->role_id == 1) {
//         $cart = Cart::find($id);
//     }
//     else {
//         $cart = Cart::where('id', $id)
//                     ->where('user_id', $currentUser->id)
//                     // ->where('status', 'active')
//                     ->first();
//     }

//     if (!$cart) {
//         return response()->json([
//             'message' => 'Cart not found or unauthorized access'
//         ], 404);
//     }

//     return new CartResource($cart);
// }

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

    // تحقق مما إذا كان معرف المستخدم يتطابق مع المعرف الحالي
    if ($currentUser->id != $id) {
        return response()->json([
            'message' => 'Unauthorized access to another user\'s cart'
        ], 403);
    }

    // استرجاع السلة الخاصة بالمستخدم
    $cart = Cart::where('user_id', $id)
                // ->where('status', 'active') // إذا كنت ترغب في التحقق من الحالة
                ->first();

    if (!$cart) {
        return response()->json([
            'message' => 'Cart not found or unauthorized access'
        ], 404);
    }

    return new ShowCartResource($cart);
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

    if ($currentUser->role_id == 1) {
        $cart = Cart::where('id', $id)->first();
    } else {

        $cart = Cart::where('id', $id)
                    ->where(function ($query) use ($currentUser) {
                        $query->where('user_id', $currentUser->id)
                              ->orWhere('admin_id', $currentUser->id);
                    })
                    // ->where('status', 'active')
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

public function removeCartItem(Request $request)
{
    $currentUser = auth()->guard('api')->user() ?? auth()->guard('admin')->user();

    if (!$currentUser) {
        return response()->json([
            'message' => 'Unauthorized User'
        ], 401);
    }

    // التحقق من وجود cart_id و product_id في الطلب
    $cartId = $request->input('cart_id');
    $productId = $request->input('product_id');

    if (!$cartId || !$productId) {
        return response()->json([
            'message' => 'cart_id and product_id are required'
        ], 422);
    }

    // البحث عن المنتج داخل السلة
    $cartProduct = CartProduct::where('cart_id', $cartId)
                              ->where('product_id', $productId)
                              ->first();

    if (!$cartProduct) {
        return response()->json([
            'message' => 'Cart product not found'
        ], 404);
    }

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
