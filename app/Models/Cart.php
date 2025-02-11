<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'admin_id',
        'status',
        'code_id',
        'totalPrice',
        'discount',
        'shippingCost',
        'finalPrice',
        'profit',
        ];

        public function user()
{
    return $this->belongsTo(User::class);
}

        public function admin()
{
    return $this->belongsTo(Admin::class);
}


    public function products()
    {
        return $this->belongsToMany(Product::class, 'cart_products')
                    ->withPivot('quantity', 'total','profit')
                    ->withTimestamps();
    }

    public function code()
{
    return $this->belongsTo(Code::class, 'code_id');
}

}
