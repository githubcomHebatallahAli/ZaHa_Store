<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderRequest;
use App\Http\Resources\Admin\OrderResource;

class OrderController extends Controller
{
    public function create(OrderRequest $request)
{
    $this->authorize('manage_users');

    $Order = Order::create([
        "name" => $request->name,
        "phoNum" => $request->phoNum,
        "address" => $request->address ,
        "details" => $request->details ,
        "discount" => $request->discount,
        "shippingCost" => $request->shippingCost ?? 0,
        'creationDate' => now()->timezone('Africa/Cairo')
            ->format('Y-m-d h:i:s'),
    ]);

    $totalProfit = 0;
    $totalSellingPrice = 0;
    $shippingCost = $request->shippingCost ?? 0;

    if ($request->has('products')) {
        foreach ($request->products as $product) {
            $productModel = Product::find($product['id']);

            if ($productModel->quantity <= 0) {
                return response()->json([
                    'message' => "Product '{$productModel->name}' is out of stock and cannot be added to the order.",
                ], 400);
            }

            if ($product['quantity'] > $productModel->quantity) {
                return response()->json([
                    'message' => "Not enough quantity for product '{$productModel->name}'. Available: {$productModel->quantity}.",
                ], 400);
            }

            $productModel->decrement('quantity', $product['quantity']);

            if ($productModel->quantity === 0) {
                $outOfStockProducts[] = $productModel->name;
            }

            $totalSellingPriceForProduct = $productModel->sellingPrice * $product['quantity'];
            $totalSellingPrice += $totalSellingPriceForProduct;

            $profitForProduct = ($productModel->sellingPrice - $productModel->purchesPrice) * $product['quantity'];
            $totalProfit += $profitForProduct;

            $Order->products()->attach($product['id'], [
                'quantity' => $product['quantity'],
                'total' => $totalSellingPriceForProduct,
                'profit' => $profitForProduct,
            ]);
        }
    }

    $discount = $Order->discount ?? 0;
    $totalSellingPrice += $shippingCost;
    $finalPrice = $totalSellingPrice - $discount;
    $netProfit = $totalProfit - $discount;

    $formattedTotalSellingPrice = number_format($totalSellingPrice, 2, '.', '');
    $formattedFinalPrice = number_format($finalPrice, 2, '.', '');
    $formattedNetProfit = number_format($netProfit, 2, '.', '');
    $formattedDiscount = number_format($discount, 2, '.', '');
    $formattedshippingCost = number_format($shippingCost, 2, '.', '');

    $Order->update([
        'totalPrice' => $formattedTotalSellingPrice,
        'finalPrice' => $formattedFinalPrice,
        'profit' => $formattedNetProfit,
    ]);

    $Order->updateOrderProductCount();

    $warningMessage = null;
    if (!empty($outOfStockProducts)) {
        $warningMessage = "The following products are now out of stock: " . implode(', ', $outOfStockProducts);
    }

    return response()->json([
        'message' => 'Order created successfully',
        'order' => new OrderResource($Order->load('products')),
        'shippingCost' => $formattedshippingCost,
        'totalPrice' => $formattedTotalSellingPrice,
        'discount' => $formattedDiscount,
        'finalPrice' => $formattedFinalPrice,
        'warning' => $warningMessage,
    ]);
}
}
