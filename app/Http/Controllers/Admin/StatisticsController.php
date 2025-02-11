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
    // public function showStatistics()
    // {
    //     $this->authorize('manage_users');
    //     $productsCount = Product::count();
    //     $categoriesCount = Category::count();
    //     $invoicesCount = Invoice::count();
    //     $sales = Invoice::sum('invoiceAfterDiscount');
    //     $netProfit = Invoice::sum('profit');
    //     $totalWithdrawals = Withdraw::sum('withdrawnAmount');
    //     $availableWithdrawal = $sales - $totalWithdrawals;


    //     $statistics = [
    //         'Categories_count' => $categoriesCount,
    //         'Products_count' => $productsCount,
    //         'Invoices_count' => $invoicesCount,
    //         'Sales' => $sales,
    //         'Net_Profit' => $netProfit,
    //         'Available_Withdrawal' => $availableWithdrawal,

    //     ];

    //     return response()->json($statistics);
    // }

    public function showStatistics()
{
    $this->authorize('manage_users');

    $productsCount = Product::count();
    $categoriesCount = Category::count();
    $invoicesCount = Invoice::count();

    // حساب إجمالي المبيعات
    $salesFromInvoices = Invoice::sum('invoiceAfterDiscount');
    $salesFromDepts = Dept::where('status', 'paid')->sum('depetAfterDiscount');
    $totalSales = $salesFromInvoices + $salesFromDepts;

    // حساب صافي الربح
    $profitFromInvoices = Invoice::sum('profit');
    $profitFromDepts = Dept::where('status', 'paid')->sum('profit'); // تأكد من وجود عمود 'profit' في جدول Dept
    $netProfit = $profitFromInvoices + $profitFromDepts;

    // إجمالي المبالغ المسحوبة
    $totalWithdrawals = Withdraw::sum('withdrawnAmount');

    // المبلغ المتاح للسحب
    $availableWithdrawal = $totalSales - $totalWithdrawals;

    $statistics = [
        'Categories_count' => $categoriesCount,
        'Products_count' => $productsCount,
        'Invoices_count' => $invoicesCount,
        'Sales' => $totalSales,
        'Net_Profit' => $netProfit,
        'Available_Withdrawal' => $availableWithdrawal,
    ];

    return response()->json($statistics);
}

}
