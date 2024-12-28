<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'category_id',
        'shipment_id',
        'name',
        'productNum',
        'sellingPrice',
        'purchesPrice',
        'profit',
    ];

    protected static function boote()
    {
        static::created(function ($news) {
            $news->category->increment('productsCount');
        });

        static::deleted(function ($news) {
            $news->category->decrement('productsCount');
        });
    }

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

}
