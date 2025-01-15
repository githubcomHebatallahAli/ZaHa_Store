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


    public function create(WithdrawRequest $request)
    {
        $this->authorize('manage_users');

        $totalSales = Invoice::sum('invoiceAfterDiscount');

        // حساب إجمالي السحوبات
        $totalWithdrawals = Withdraw::sum('withdrawnAmount');

        // حساب المبلغ المتاح للسحب
        $availableWithdrawal = $totalSales - $totalWithdrawals;

        // المبلغ المطلوب سحبه
        $amountToWithdraw = $request->amount;

        // التحقق من أن المبلغ المطلوب سحبه لا يتجاوز المبلغ المتاح
        if ($amountToWithdraw > $availableWithdrawal) {
            return response()->json([
                'message' => 'المبلغ المطلوب سحبه يتجاوز المبلغ المتاح.',
                'availableWithdrawal' => $availableWithdrawal,
            ], 400);
        }

        $Withdraw = Withdraw::create([
            'personName' => $request->personName,
            'creationDate' => now()->timezone('Africa/Cairo')
            ->format('Y-m-d h:i:s'),
            'availableWithdrawal' => $availableWithdrawal,
            'withdrawnAmount' => $amountToWithdraw,
            'remainingAmount' => $availableWithdrawal - $amountToWithdraw,
            'totalSalesCopy' => $totalSales,
            'description' => $request->description ,
        ]);

        return response()->json([
            'message' => 'تم السحب بنجاح.',
            'data' =>new WithdrawResource($Withdraw),
            'availableWithdrawal' => $availableWithdrawal - $amountToWithdraw, // الرصيد المتاح بعد السحب
        ]);

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
       $Withdraw =Withdraw::findOrFail($id);

       if (!$Withdraw) {
        return response()->json([
            'message' => "Withdraw not found."
        ], 404);
    }
       $Withdraw->update([
        "personName" => $request-> personName,
        // "availableWithdrawal" => $request-> availableWithdrawal,
        "withdrawnAmount" => $request-> withdrawnAmount,
        "remainingAmount" => $request-> remainingAmount,
        "description" => $request-> description ,
        'creationDate' => now()->timezone('Africa/Cairo')
        ->format('Y-m-d h:i:s'),
        ]);

    //    $Withdraw->save();
       return response()->json([
        'data' =>new WithdrawResource($Withdraw),
        'message' => " Update Withdraw By Id Successfully."
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
