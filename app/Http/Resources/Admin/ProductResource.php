<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            "productNum" => $this -> productNum,
            "sellingPrice" => $this -> sellingPrice,
            "purchesPrice" => $this -> purchesPrice,
            "profit" => $this -> profit,
            'category' => new CategoryResource($this->category),
            'shipment' => new ShipmentResource($this->shipment),


        ];
    }
}
