<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
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
            'supplierName' => $this -> supplierName,
            'importer' => $this -> importer ,
            'place' => $this -> place,
            'totalProductNum' => $this -> totalProductNum,
            'totalPrice' => $this -> totalPrice,
            'description' => $this -> description,
            'creationDate' => $this -> creationDate,
        ];
    }
}
