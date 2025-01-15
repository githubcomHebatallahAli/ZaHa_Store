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
            'creationDate'  => $this -> creationDate,
            'availableWithdrawal'  => $this -> availableWithdrawal,
            'withdrawnAmount'  => $this -> withdrawnAmount,
            'remainingAmount'  => $this -> remainingAmount,
            'description'  => $this -> description,
            'totalSalesCopy' => $this ->totalSalesCopy
        ];
    }
}
