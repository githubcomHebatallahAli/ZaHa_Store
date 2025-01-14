<?php

namespace App\Http\Controllers\Admin;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Category;
use App\Http\Controllers\Controller;

class StatisticsController extends Controller
{
    public function showStatistics()
    {
        $this->authorize('manage_users');
        $productsCount = Product::count();
        $categoriesCount = Category::count();
        $invoicesCount = Invoice::count();
        $sales = Invoice::sum('invoiceAfterDiscount');
        $netProfit = Invoice::sum('profit');
        // $availableWithdrawal=

        // $productsQuantities = Product::select('name', 'quantity')
        // ->get()
        // ->map(function ($product) {
        //     return [
        //         'name' => $product->name,
        //         'quantity' => $product->quantity,
        //     ];
        // });


        $statistics = [
            'Categories_count' => $categoriesCount,
            'Products_count' => $productsCount,
            'Invoices_count' => $invoicesCount,
            'Sales' => $sales,
            'Net_Profit' => $netProfit,
            // 'Available_Withdrawal'=> $availableWithdrawal,

            // 'Products_Quantities' => $productsQuantities,


        ];

        return response()->json($statistics);
    }
}
