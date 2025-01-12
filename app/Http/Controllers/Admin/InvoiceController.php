<?php

namespace App\Http\Controllers\Admin;

use App\Models\Invoice;
use App\Models\Product;
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

        $Invoices = Invoice::paginate(10);
        return response()->json([
            'data' => $Invoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id, // اسم العميل
                    'customerName' => $invoice->customerName, // اسم العميل
                    'creationDate' => $invoice->creationDate, // تاريخ الإنشاء
                ];
            }),
            'message' => "Show All Invoices Successfully."
        ]);
    }


    public function create(InvoiceRequest $request)
    {
        $this->authorize('manage_users');

           $Invoice =Invoice::create ([
                "customerName" => $request-> customerName,
                "sellerName" => $request-> sellerName,
                "discount" => $request-> discount,
                'creationDate' => now()->timezone('Africa/Cairo')
                ->format('Y-m-d h:i:s'),
            ]);


            if ($request->has('products')) {
                foreach ($request->products as $product) {
                    $productModel = Product::find($product['id']);
                    $totalPriceForProduct = $productModel->purchesPrice * $product['quantity'];
                    $Invoice->products()->attach($product['id'], [
                        'quantity' => $product['quantity'],
                        'total' => $totalPriceForProduct,
                    ]);
                }
            }


            $totalInvoicePrice = $Invoice->load('products')->calculateTotalPrice();

            $finalPrice = $totalInvoicePrice - ($Invoice->discount ?? 0);


            $Invoice->update([
                'totalInvoicePrice' => $totalInvoicePrice,
                'invoiceAfterDiscount' => $finalPrice,
            ]);

            $Invoice->updateInvoiceProductCount();

            return response()->json([
                'message' => 'Invoice update successfully',
                'invoice' => new InvoiceResource($Invoice->load('products')),
                'totalInvoicePrice' => $totalInvoicePrice,
                'discount' => $Invoice->discount,
                'invoiceAfterDiscount' => $finalPrice,
            ]);
        }



    public function edit(string $id)
    {
        $this->authorize('manage_users');
        $Invoice = Invoice::with('products')->find($id);

        if (!$Invoice) {
            return response()->json([
                'message' => "Invoice not found."
            ], 404);
        }
        $totalInvoicePrice = $Invoice->load('products')->calculateTotalPrice();

        $finalPrice = $totalInvoicePrice - ($Invoice->discount ?? 0);


        $Invoice->update([
            'totalInvoicePrice' => $totalInvoicePrice,
            'invoiceAfterDiscount' => $finalPrice,
        ]);

        // $Invoice->updateInvoiceProductCount();

        return response()->json([
            'message' => 'Invoice update successfully',
            'invoice' => new InvoiceResource($Invoice->load('products')),
            'totalInvoicePrice' => $totalInvoicePrice,
            'discount' => $Invoice->discount,
            'invoiceAfterDiscount' => $finalPrice,
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
                "discount" => $request-> discount,
                'creationDate' => now()->timezone('Africa/Cairo')
                ->format('Y-m-d h:i:s'),
            ]);

            if ($request->has('products')) {
                $productsData = [];
                foreach ($request->products as $product) {
                    $productModel = Product::find($product['id']);
                    $totalPriceForProduct = $productModel->purchesPrice * $product['quantity'];

                    $productsData[$product['id']] = [
                        'quantity' => $product['quantity'],
                        'total' => $totalPriceForProduct,
                    ];
                }

                $Invoice->products()->sync($productsData);
            }


            $totalInvoicePrice = $Invoice->load('products')->calculateTotalPrice();

            $finalPrice = $totalInvoicePrice - ($Invoice->discount ?? 0);


            $Invoice->update([
                'totalInvoicePrice' => $totalInvoicePrice,
                'invoiceAfterDiscount' => $finalPrice,
            ]);

            $Invoice->updateInvoiceProductCount();

            return response()->json([
                'message' => 'Invoice created successfully',
                'invoice' => new InvoiceResource($Invoice->load('products')),
                'totalInvoicePrice' => $totalInvoicePrice,
                'discount' => $Invoice->discount,
                'invoiceAfterDiscount' => $finalPrice,
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
