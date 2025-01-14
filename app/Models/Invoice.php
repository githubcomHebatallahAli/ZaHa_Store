<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        'invoiceProductCount',
        'totalInvoicePrice',
        'discount',
        'invoiceAfterDiscount',
        'profit'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'invoice_products')
                    ->withPivot('quantity', 'total','profit');
    }

    protected static function booted()
{

    static::created(function ($invoice) {
        $invoice->load('products');
        $invoice->updateInvoiceProductCount();
    });

    static::deleted(function ($invoice) {
        if (method_exists($invoice, 'isForceDeleting') && $invoice->isForceDeleting()) {
            return;
        }

        if (!$invoice->trashed()) {
            $invoice->updateInvoiceProductCount();
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

public function updateInvoiceProductCount()
{
    $this->invoiceProductCount = $this->products()
    ->whereNull('deleted_at')
    ->count();
    $this->saveQuietly();
}

public function getInvoiceProductCountAttribute()
{
    return $this->attributes['invoiceProductCount'] ?? 0;
}


// protected static function boot()
// {
//     parent::boot();

//     // عند الحذف (Soft Delete)
//     static::deleting(function ($invoice) {
//         if (!$invoice->isForceDeleting()) { // يتم التحقق إذا كان الحذف Soft Delete فقط
//             foreach ($invoice->products as $product) {
//                 $product->increment('quantity', $product->pivot->quantity);
//             }
//         }
//     });

//     // عند الحذف الإجباري (Force Delete)
//     static::forceDeleted(function ($invoice) {
//         foreach ($invoice->products as $product) {
//             $product->increment('quantity', $product->pivot->quantity);
//         }
//     });

//     // عند استرجاع الفاتورة (Restore)
//     static::restored(function ($invoice) {
//         foreach ($invoice->products as $product) {
//             $product->decrement('quantity', $product->pivot->quantity);
//         }
//     });
// }

}
