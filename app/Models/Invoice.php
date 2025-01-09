<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'customerName',
        'sellerName',
        'creationDate',
        'product_id',
        'invoiceProductNum',
        'invoicePrice',
        'discount',
        'invoiceAfterDiscount'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
