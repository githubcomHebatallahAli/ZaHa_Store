<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'cart_id',
        'name',
        'phoNum',
        'address',
        'details',
        // 'totalPrice',
        'discount',
        'shippingCost',
        // 'finalPrice',
        'status',
        'creationDate'
    ];

    public function cart()
{
    return $this->belongsTo(Cart::class);
}

public function code()
{
    return $this->belongsTo(Code::class, 'code_id');
}



}
