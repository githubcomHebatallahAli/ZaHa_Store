<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name', 'phoNum', 'address', 'details', 'orderProductCount',
        'totalPrice', 'discount', 'shippingCost', 'finalPrice', 'profit'
    ];


    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products')
                    ->withPivot('quantity', 'total','profit')
                    ->withTimestamps();
    }
}
