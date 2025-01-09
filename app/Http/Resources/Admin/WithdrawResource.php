<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this -> id,
            'personName'  => $this -> personName,
            'creationDate'  => $this -> ceationDate,
            'totalAmount'  => $this -> totalAmount,
            'withdrawnAmount'  => $this -> withdrawnAmount,
            'remainingAmount'  => $this -> remainingAmoumt,
            'description'  => $this -> description,
        ];
    }
}
