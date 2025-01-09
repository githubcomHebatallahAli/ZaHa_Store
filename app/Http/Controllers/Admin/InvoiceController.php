<?php

namespace App\Http\Controllers\Admin;

use App\Models\Invoice;
use App\Traits\ManagesModelsTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InvoiceRequest;
use App\Http\Resources\Admin\InvoiceResource;


class InvoiceController extends Controller
{
    use ManagesModelsTrait;
    public function showAll()
    {
        $this->authorize('manage_users');

        $Invoices = Invoice::get();
        return response()->json([
            'data' => InvoiceResource::collection($Invoices),
            'message' => "Show All Invoices Successfully."
        ]);
    }


    public function create(InvoiceRequest $request)
    {
        $this->authorize('manage_users');

           $Invoice =Invoice::create ([
                "customerName" => $request-> customerName,
                "sellerName" => $request-> sellerName,
                "product_id" => $request-> product_id,
                "invoiceProductNum" => $request-> invoiceProductNum,
                "invoicePrice" => $request-> invoicePrice,
                "discount" => $request-> discount,
                "invoiceAfterDiscount" => $request-> invoiceAfterDiscount,
                'creationDate' => now()->timezone('Africa/Cairo')
                ->format('Y-m-d h:i:s'),

            ]);
           $Invoice->save();
           return response()->json([
            'data' =>new InvoiceResource($Invoice),
            'message' => "Invoice Created Successfully."
        ]);
        }


    public function edit(string $id)
    {
        $this->authorize('manage_users');
        $Invoice = Invoice::find($id);

        if (!$Invoice) {
            return response()->json([
                'message' => "Invoice not found."
            ], 404);
        }

        return response()->json([
            'data' =>new InvoiceResource($Invoice),
            'message' => "Edit Invoice By ID Successfully."
        ]);
    }



    public function update(InvoiceRequest $request, string $id)
    {
        $this->authorize('manage_users');
       $Invoice =Invoice::findOrFail($id);

       if (!$Invoice) {
        return response()->json([
            'message' => "Invoice not found."
        ], 404);
    }
       $Invoice->update([
        "customerName" => $request-> customerName,
        "sellerName" => $request-> sellerName,
        "product_id" => $request-> product_id,
        "invoiceProductNum" => $request-> invoiceProductNum,
        "invoicePrice" => $request-> invoicePrice,
        "discount" => $request-> discount,
        "invoiceAfterDiscount" => $request-> invoiceAfterDiscount,
        'creationDate' => now()->timezone('Africa/Cairo')
        ->format('Y-m-d h:i:s'),
        ]);

    //    $Invoice->save();
       return response()->json([
        'data' =>new InvoiceResource($Invoice),
        'message' => " Update Invoice By Id Successfully."
    ]);

  }

  public function destroy(string $id)
  {
      return $this->destroyModel(Invoice::class, InvoiceResource::class, $id);
  }

  public function showDeleted()
  {
    $this->authorize('manage_users');
$Invoices=Invoice::onlyTrashed()->get();
return response()->json([
    'data' =>InvoiceResource::collection($Invoices),
    'message' => "Show Deleted Invoices Successfully."
]);

}

public function restore(string $id)
{
   $this->authorize('manage_users');
$Invoice = Invoice::withTrashed()->where('id', $id)->first();
if (!$Invoice) {
    return response()->json([
        'message' => "Invoice not found."
    ], 404);
}
$Invoice->restore();
return response()->json([
    'data' =>new InvoiceResource($Invoice),
    'message' => "Restore Invoice By Id Successfully."
]);
}

  public function forceDelete(string $id)
  {
      return $this->forceDeleteModel(Invoice::class, $id);
  }

}
