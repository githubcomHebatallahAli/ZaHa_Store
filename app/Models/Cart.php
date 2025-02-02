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
        'status'
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
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
}
