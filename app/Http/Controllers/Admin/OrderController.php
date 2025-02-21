<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cart;
use App\Models\Code;
use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderRequest;
use App\Http\Resources\User\OrderResource;

class OrderController extends Controller
{
    public function create(OrderRequest $request)
{
    $cart = Cart::findOrFail($request->cart_id);
    $discountValue = 0;
    $code = null;

    if ($request->code) {
        $code = Code::where('code', $request->code)->where('status', 'active')->first();

        if ($code) {
            if ($code->type === 'percentage') {
                $discountValue = ($cart->totalPrice * $code->discount) / 100;
            } else {
                $discountValue = $code->discount;
            }
        }
    }

    $finalPrice = max(0, $cart->totalPrice - $discountValue + $cart->shippingCost);

    $Order = Order::create([
        'cart_id' => $cart->id,
        'code_id' => $code ? $code->id : null,
        "name" => $request->name,
        "phoNum" => $request->phoNum,
        "address" => $request->address ,
        "details" => $request->details ,
        'status' => 'pending',
        'shippingCost' => $cart->shippingCost,
        'discount' => $discountValue,
        'creationDate' => now()->timezone('Africa/Cairo')
            ->format('Y-m-d h:i:s'),
    ]);

    return response()->json([
        'message' => 'Order created successfully',
        'order' => new OrderResource($Order),
    ]);
}
}
