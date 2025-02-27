<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Code extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'code',
        'discount',
        'type',
        'status'
    ];

    public function carts()
{
    return $this->hasMany(Cart::class, 'code_id');
}

public function orders()
{
    return $this->hasMany(Order::class, 'code_id');
}

}
