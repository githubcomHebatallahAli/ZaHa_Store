<?php

namespace App\Http\Controllers\Admin;

use App\Models\Invoice;
use App\Models\Withdraw;
use App\Traits\ManagesModelsTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WithdrawRequest;
use App\Http\Resources\Admin\WithdrawResource;


class WithdrawController extends Controller
{
    use ManagesModelsTrait;
    public function showAll()
    {
        $this->authorize('manage_users');

        $Withdraws = Withdraw::get();
        return response()->json([
            'data' => $Withdraws->map(function ($Withdraws) {
                return [
                    'id' => $Withdraws->id,
                    'personName' => $Withdraws-> personName,
                    'withdrawnAmount' => $Withdraws-> withdrawnAmount,
                    'creationDate' => $Withdraws-> creationDate,
                ];
            }),
            'pagination' => [
                'total' => $Withdraws->total(),
                'count' => $Withdraws->count(),
                'per_page' => $Withdraws->perPage(),
                'current_page' => $Withdraws->currentPage(),
                'total_pages' => $Withdraws->lastPage(),
                'next_page_url' => $Withdraws->nextPageUrl(),
                'prev_page_url' => $Withdraws->previousPagUrl(),
            ],
            'message' => "Show All Withdraws Successfully."
        ]);
    }


    // public function create(WithdrawRequest $request)
    // {
    //     $this->authorize('manage_users');

    //     $totalSales = Invoice::sum('invoiceAfterDiscount');

    //     $totalWithdrawals = Withdraw::sum('withdrawnAmount');

    //     $availableWithdrawal = $totalSales - $totalWithdrawals;

    //     $amountToWithdraw = $request->amount;

    //     if ($amountToWithdraw > $availableWithdrawal) {
    //         return response()->json([
    //             'message' => 'المبلغ المطلوب سحبه يتجاوز المبلغ المتاح.',
    //             'availableWithdrawal' => $availableWithdrawal,
    //         ], 400);
    //     }

    //     $Withdraw = Withdraw::create([
    //         'personName' => $request->personName,
    //         'creationDate' => now()->timezone('Africa/Cairo')
    //         ->format('Y-m-d h:i:s'),
    //         'availableWithdrawal' => $availableWithdrawal,
    //         'withdrawnAmount' => $amountToWithdraw,
    //         'remainingAmount' => $availableWithdrawal - $amountToWithdraw,
    //         'totalSalesCopy' => $totalSales,
    //         'description' => $request->description ,
    //     ]);

    //     return response()->json([
    //         'message' => 'تم السحب بنجاح.',
    //         'data' =>new WithdrawResource($Withdraw),
    //         'availableWithdrawal' => $availableWithdrawal - $amountToWithdraw, // الرصيد المتاح بعد السحب
    //     ]);

    //     }

    public function create(WithdrawRequest $request)
{
    $this->authorize('manage_users');

    $totalSales = Invoice::sum('invoiceAfterDiscount');

    $totalWithdrawals = Withdraw::sum('withdrawnAmount');

    $availableWithdrawal = $totalSales - $totalWithdrawals;

    $amountToWithdraw = $request->withdrawnAmount;

    if ($amountToWithdraw > $availableWithdrawal) {
        return response()->json([
            'message' => 'المبلغ المطلوب سحبه يتجاوز المبلغ المتاح.',
            'availableWithdrawal' => $availableWithdrawal,
        ], 400);

    $remainingAmountAfterWithdraw = $availableWithdrawal - $amountToWithdraw;

    $withdraw = Withdraw::create([
        'personName' => $request->personName,
        'creationDate' => now()->timezone('Africa/Cairo')->format('Y-m-d h:i:s'),
        'withdrawnAmount' => $amountToWithdraw,
        'remainingAmount' => $remainingAmountAfterWithdraw,
        'totalSalesCopy' => $totalSales,
        'description' => $request->description,
    ]);

    return response()->json([
        'message' => 'تم السحب بنجاح.',
        'data' => new WithdrawResource($withdraw),
        'availableWithdrawal' => $remainingAmountAfterWithdraw, 
    ]);
}
}
    public function edit(string $id)
    {
        $this->authorize('manage_users');
        $Withdraw = Withdraw::find($id);

        if (!$Withdraw) {
            return response()->json([
                'message' => "Withdraw not found."
            ], 404);
        }

        return response()->json([
            'data' =>new WithdrawResource($Withdraw),
            'message' => "Edit Withdraw By ID Successfully."
        ]);
    }

    public function update(WithdrawRequest $request, string $id)
    {
        $this->authorize('manage_users');

        // البحث عن السحب المطلوب
        $withdraw = Withdraw::findOrFail($id);

        if (!$withdraw) {
            return response()->json([
                'message' => "لم يتم العثور على السحب."
            ], 404);
        }

        // حساب المبلغ المتاح للسحب قبل التحديث
        $totalSales = Invoice::sum('invoiceAfterDiscount');
        $totalWithdrawals = Withdraw::sum('withdrawnAmount');
        $availableWithdrawalBeforeUpdate = $totalSales - $totalWithdrawals;

        // الفرق بين المبلغ القديم والمبلغ الجديد
        $oldAmount = $withdraw->withdrawnAmount; // المبلغ القديم (20)
        $newAmount = $request->amount; // المبلغ الجديد (10)
        $difference = $oldAmount - $newAmount; // الفرق بين المبلغ القديم والجديد (20 - 10 = 10)

        // إعادة المبلغ القديم إلى الحد المتاح للسحب
        $availableWithdrawalBeforeUpdate += $oldAmount; // 930 + 20 = 950

        // إذا كان المبلغ الجديد أكبر من المبلغ المتاح
        if ($newAmount > $availableWithdrawalBeforeUpdate) {
            return response()->json([
                'message' => 'المبلغ المطلوب سحبه يتجاوز المبلغ المتاح.',
                'availableWithdrawal' => $availableWithdrawalBeforeUpdate,
            ], 400);
        }

        // سحب المبلغ الجديد
        $availableWithdrawalAfterUpdate = $availableWithdrawalBeforeUpdate - $newAmount; // 950 - 10 = 940

        // تحديث بيانات السحب في قاعدة البيانات
        $withdraw->update([
            'personName' => $request->personName,
            'withdrawnAmount' => $newAmount,
            'remainingAmount' => $availableWithdrawalAfterUpdate, // 940 (بدون خصم newAmount مرة أخرى)
            // 'availableWithdrawal' => $availableWithdrawalAfterUpdate, // تم إزالة هذا السطر للحفاظ على القيمة الأصلية
            'totalSalesCopy' => $totalSales,
            'description' => $request->description,
            'creationDate' => now()->timezone('Africa/Cairo')->format('Y-m-d h:i:s'),
        ]);

        // إرجاع الـ response مع تحديث الحد المتاح للسحب
        return response()->json([
            'data' => [
                'id' => $withdraw->id,
                'personName' => $withdraw->personName,
                'creationDate' => $withdraw->creationDate,
                'availableWithdrawal' => $availableWithdrawalBeforeUpdate, // 950 (قبل التحديث)
                'withdrawnAmount' => $withdraw->withdrawnAmount, // 10
                'remainingAmount' => $availableWithdrawalAfterUpdate, // 940 (بعد التحديث)
                'description' => $withdraw->description,
                'totalSalesCopy' => $withdraw->totalSalesCopy,
            ],
            'message' => "تم تحديث السحب بنجاح.",
            'availableWithdrawal' => $availableWithdrawalAfterUpdate, // 940 (بعد التحديث)
        ]);
    }





  public function destroy(string $id)
  {
      return $this->destroyModel(Withdraw::class, WithdrawResource::class, $id);
  }

  public function showDeleted()
  {
    $this->authorize('manage_users');
$Withdraws=Withdraw::onlyTrashed()->get();
return response()->json([
    'data' =>WithdrawResource::collection($Withdraws),
    'message' => "Show Deleted Withdraws Successfully."
]);

}

public function restore(string $id)
{
   $this->authorize('manage_users');
$Withdraw = Withdraw::withTrashed()->where('id', $id)->first();
if (!$Withdraw) {
    return response()->json([
        'message' => "Withdraw not found."
    ], 404);
}
$Withdraw->restore();
return response()->json([
    'data' =>new WithdrawResource($Withdraw),
    'message' => "Restore Withdraw By Id Successfully."
]);
}

  public function forceDelete(string $id)
  {
      return $this->forceDeleteModel(Withdraw::class, $id);
  }
}
