<?php

namespace App\Http\Controllers\Admin;

use App\Models\Dept;
use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use App\Http\Requests\Admin\DeptRequest;
use App\Http\Resources\Admin\DeptResource;
use App\Http\Requests\Admin\UpdatePaidAmountRequest;


class DeptController extends Controller
{
    public function showAll()
    {
        $this->authorize('manage_users');

        $depts = Dept::orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'data' => $depts->map(function ($dept) {
                return [
                    'id' => $dept->id,
                    'customerName' => $dept->customerName,
                    'status' => $dept->status,
                    'paidAmount' => $dept->paidAmount,
                    'remainingAmount' => $dept->remainingAmount,
                    'depetAfterDiscount' => $dept->depetAfterDiscount,
                    'creationDate' => $dept->creationDate,
                ];
            }),
            'pagination' => [
                'total' => $depts->total(),
                'count' => $depts->count(),
                'per_page' => $depts->perPage(),
                'current_page' => $depts->currentPage(),
                'total_pages' => $depts->lastPage(),
                'next_page_url' => $depts->nextPageUrl(),
                'prev_page_url' => $depts->previousPageUrl(),
            ],
            'message' => "Show All Invoices Successfully."
        ]);
    }

    public function create(DeptRequest $request)
    {
        $this->authorize('manage_users');

        $Dept = Dept::create([
            "customerName" => $request->customerName,
            "sellerName" => $request->sellerName,
            "discount" => $request->discount ?? 0,
            "extraAmount" => $request->extraAmount ?? 0,
            'creationDate' => now()->timezone('Africa/Cairo')->format('Y-m-d H:i:s'),
            'paidAmount' => $request->paidAmount ?? 0,
        ]);

        $totalProfit = 0;
        $totalDeptPrice = 0;
        $extraAmount = $request->extraAmount ?? 0;
        $outOfStockProducts = [];

        if ($request->has('products')) {
            foreach ($request->products as $product) {
                $productModel = Product::find($product['id']);

                if ($productModel->quantity <= 0) {
                    return response()->json([
                        'message' => "Product '{$productModel->name}' is out of stock and cannot be added.",
                    ], 400);
                }

                if ($product['quantity'] > $productModel->quantity) {
                    return response()->json([
                        'message' => "Not enough quantity for '{$productModel->name}'. Available: {$productModel->quantity}.",
                    ], 400);
                }

                $productModel->decrement('quantity', $product['quantity']);

                if ($productModel->quantity === 0) {
                    $outOfStockProducts[] = $productModel->name;
                }

                $totalDeptPriceForProduct = $productModel->sellingPrice * $product['quantity'];
                $totalDeptPrice += $totalDeptPriceForProduct;

                $profitForProduct = ($productModel->sellingPrice - $productModel->purchesPrice) * $product['quantity'];
                $totalProfit += $profitForProduct;

                $Dept->products()->attach($product['id'], [
                    'quantity' => $product['quantity'],
                    'total' => $totalDeptPriceForProduct,
                    'profit' => $profitForProduct,
                ]);
            }
        }

        $discount = $Dept->discount ?? 0;
        $totalDeptPrice += $extraAmount;
        $finalDeptPrice = $totalDeptPrice - $discount;
        $netProfit = $totalProfit - $discount;
        $remainingAmount = $finalDeptPrice - $Dept->paidAmount;

        $formattedTotalDeptPrice = number_format($totalDeptPrice, 2, '.', '');
        $formattedFinalDeptPrice = number_format($finalDeptPrice, 2, '.', '');
        $formattedNetProfit = number_format($netProfit, 2, '.', '');
        $formattedDiscount = number_format($discount, 2, '.', '');
        $formattedExtraAmount = number_format($extraAmount, 2, '.', '');
        $formattedRemainingAmount = number_format($remainingAmount, 2, '.', '');

        $Dept->totalDepetPrice = $formattedTotalDeptPrice;
        $Dept->depetAfterDiscount = $formattedFinalDeptPrice;
        $Dept->profit = $formattedNetProfit;
        $Dept->remainingAmount = $formattedRemainingAmount;
        $Dept->status = $remainingAmount > 0 ? 'pending' : 'paid';
        $Dept->save();

        $Dept->updateDeptProductCount();

        $warningMessage = null;
        if (!empty($outOfStockProducts)) {
            $warningMessage = "The following products are now out of stock: " . implode(', ', $outOfStockProducts);
        }

        return response()->json([
            'message' => 'Dept record created successfully',
            'dept' => new DeptResource($Dept->load('products')),
            'extraAmount' => $formattedExtraAmount,
            'totalDepetPrice' => $formattedTotalDeptPrice,
            'discount' => $formattedDiscount,
            'depetAfterDiscount' => $formattedFinalDeptPrice,
            'paidAmount' => number_format($Dept->paidAmount, 2, '.', ''),
            'remainingAmount' => $formattedRemainingAmount,
            'warning' => $warningMessage,
        ]);
    }


public function updatePaidAmount(UpdatePaidAmountRequest $request, $id)
{
    $this->authorize('manage_users');
    $Dept = Dept::findOrFail($id);

    $paidAmount = $request->paidAmount;
    $Dept->paidAmount += $paidAmount;

    $remainingAmount = $Dept->depetAfterDiscount - $Dept->paidAmount;

    $Dept->remainingAmount = number_format($remainingAmount, 2, '.', '');
    $Dept->status = $remainingAmount > 0 ? 'pending' : 'paid';
    $Dept->save();

    return response()->json([
        'message' => 'Paid amount updated successfully',
        'dept' => new DeptResource($Dept->load('products')),
        'extraAmount' => number_format($Dept->extraAmount, 2, '.', ''),
        'totalDepetPrice' => number_format($Dept->totalDepetPrice, 2, '.', ''),
        'discount' => number_format($Dept->discount, 2, '.', ''),
        'depetAfterDiscount' => number_format($Dept->depetAfterDiscount, 2, '.', ''),
        'paidAmount' => number_format($Dept->paidAmount, 2, '.', ''), // ✅ تنسيق المبلغ المدفوع
        'remainingAmount' => number_format($remainingAmount, 2, '.', ''),
    ]);
}


public function edit(string $id)
{
    $this->authorize('manage_users');

    $dept = Dept::with('products')->find($id);

    if (!$dept) {
        return response()->json([
            'message' => "Dept record not found."
        ], 404);
    }

    $totalDeptPrice = 0;
    $extraAmount = $dept->extraAmount ?? 0;
    $paidAmount = $dept->paidAmount ?? 0;
    $discount = $dept->discount ?? 0;

    // **حساب إجمالي الديون بناءً على المنتجات المرتبطة**
    if ($dept->products->isNotEmpty()) {
        foreach ($dept->products as $product) {
            $totalDeptPrice += $product->pivot->total;
        }
    }

    // **إضافة المبلغ الإضافي إلى إجمالي الديون**
    $totalDeptPrice += $extraAmount;

    // **حساب القيم المطلوبة**
    $remainingAmount = max(0, $totalDeptPrice - $paidAmount);
    $deptAfterDiscount = $totalDeptPrice - $discount;

    // **تنسيق القيم**
    $formattedPaidAmount = number_format($paidAmount, 2, '.', '');
    $formattedTotalDeptPrice = number_format($totalDeptPrice, 2, '.', '');
    $formattedRemainingAmount = number_format($remainingAmount, 2, '.', '');
    $formattedDeptAfterDiscount = number_format($deptAfterDiscount, 2, '.', '');
    $formattedExtraAmount = number_format($extraAmount, 2, '.', '');
    $formattedDiscount = number_format($discount, 2, '.', '');

    // **تحديث جدول `dept`**
    $dept->update([
        'totalDepetPrice' => $formattedTotalDeptPrice,  // ✅ تصحيح الاسم
        'remainingAmount' => $formattedRemainingAmount,
        'depetAfterDiscount' => $formattedDeptAfterDiscount,  // ✅ تصحيح الاسم
    ]);

    return response()->json([
        'message' => 'Dept details fetched successfully',
        'dept' => new DeptResource($dept->load('products')),
        'extraAmount' => $formattedExtraAmount,
        'totalDeptPrice' => $formattedTotalDeptPrice,
        'discount' => $formattedDiscount,
        'deptAfterDiscount' => $formattedDeptAfterDiscount,
        'paidAmount' => $formattedPaidAmount,
        'remainingAmount' => $formattedRemainingAmount,
    ]);
}


}
