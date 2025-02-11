<?php

namespace App\Http\Controllers\Admin;

use App\Models\Dept;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Category;
use App\Models\Withdraw;
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
        // $paidAmount = Dept::sum('paidAmount');
        // $remainingAmount = Dept::sum('remainingAmount');
        $netProfit = Invoice::sum('profit');
        $totalWithdrawals = Withdraw::sum('withdrawnAmount');
        $availableWithdrawal = $sales - $totalWithdrawals;


        $statistics = [
            'Categories_count' => $categoriesCount,
            'Products_count' => $productsCount,
            'Invoices_count' => $invoicesCount,
            // 'paid_amount' =>$paidAmount,
            // 'remaining_amount'=>$remainingAmount,
            'Sales' => $sales,
            'Net_Profit' => $netProfit,
            'Available_Withdrawal' => $availableWithdrawal,

        ];

        return response()->json($statistics);
    }
}
