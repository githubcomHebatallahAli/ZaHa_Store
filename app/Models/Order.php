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
        // 'orderProductCount',
        // 'totalPrice',
        // 'discount',
        // 'shippingCost',
        // 'finalPrice',
        // 'profit',
        'status',
        'creationDate'
    ];


    // public function products()
    // {
    //     return $this->belongsToMany(Product::class, 'order_products')
    //                 ->withPivot('quantity', 'total','profit')
    //                 ->withTimestamps();
    // }

    public function cart()
{
    return $this->belongsTo(Cart::class);
}


    protected static function booted()
    {

        static::created(function ($order) {
            $order->load('products');
            $order->updateOrderProductCount();
        });

        static::deleted(function ($order) {
            if (method_exists($order, 'isForceDeleting') && $order->isForceDeleting()) {
                return;
            }

            if (!$order->trashed()) {
                $order->updateOrderProductCount();
            }
        });

    }

    public function calculateTotalPrice()
    {
        $total = 0;

        foreach ($this->products as $product) {
            $total += $product->pivot->total;
        }

        return $total;
    }

    public function updateOrderProductCount()
    {
        $this->orderProductCount = $this->products()
        ->whereNull('deleted_at')
        ->count();
        $this->saveQuietly();
    }

    public function getOrderProductCountAttribute()
    {
        return $this->attributes['orderProductCount'] ?? 0;
    }
}
