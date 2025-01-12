<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this -> id,
            'customerName' => $this -> customerName,
            'sellerName' => $this -> sellerName,
            'invoiceProductCount' => $this -> invoiceProductCount,
            // 'invoicePrice' => $this -> invoicePrice,
            // 'discount' => $this -> discount,
            // 'invoiceAfterDiscount' => $this -> invoiceAfterDiscount,
            'creationDate' => $this -> creationDate,
            'products' => $this->products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $product->pivot->quantity,
                    'total' => $product->pivot->total,
                ];
            }),
        ];
    }
}
