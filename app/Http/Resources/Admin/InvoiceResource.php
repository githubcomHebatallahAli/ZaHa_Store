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
            'product' => new ProductResource($this->product),
            'customerName' => $this -> customerName,
            'sellerName' => $this -> sellerName,
            'invoiceProductNum' => $this -> invoiceProductNum,
            'invoicePrice' => $this -> invoicePrice,
            'discount' => $this -> discount,
            'invoiceAfterDiscount' => $this -> invoiceAfterDiscount,
            'creationDate' => $this -> creationDate,
        ];
    }
}
