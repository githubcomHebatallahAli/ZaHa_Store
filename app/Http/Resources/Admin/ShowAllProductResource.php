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
            'image' => $this->image,
            "name" => $this -> name ,
    'categoryName' => $product->category->name ?? null,
            'quantity' => $this -> quantity,
            "sellingPrice" => $this -> sellingPrice,
            "purchesPrice" => $this -> purchesPrice,


        ];
    }
}
