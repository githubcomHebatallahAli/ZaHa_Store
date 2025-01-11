<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowAllProductResource extends JsonResource
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
            "name" => $this -> name ,
            // "productNum" => $this -> productNum,
            "quantity" => $this -> quantity,
            "sellingPrice" => $this -> sellingPrice,
            "purchesPrice" => $this -> purchesPrice,
            // 'totalPrice' => $this -> totalPrice,
            // "profit" => $this -> profit,
            'category' => new CategoryResource($this->category),
            // 'shipment' => new ShipmentResource($this->shipment),
            'shipment_id' => $this->shipment->id,
            'image' => $this -> image,
            'creationDate' => $this -> creationDate,

        ];
    }
}
