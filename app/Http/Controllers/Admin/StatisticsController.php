<?php

namespace App\Http\Controllers\Admin;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\InvoiceProduct;
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


        $statistics = [
            'Products_count' => $productsCount,
            'Invoices_count' => $invoicesCount,
            'Sales' => $sales,
            'Net_Profit' => $netProfit,
            // 'Available_Withdrawal'=> $availableWithdrawal,
            'Categories_count' => $categoriesCount,


        ];

        return response()->json($statistics);
    }
}
