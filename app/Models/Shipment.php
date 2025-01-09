<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'supplierName',
        'importer',
        'place',
        'shipmentProductNum',
        'totalPrice',
        'description',
        'creationDate'
    ];


    protected $date = ['creationDate'];


    public function getFormattedCreationDateAttribute()
    {
        return Carbon::parse($this->creationDate)
        ->timezone('Africa/Cairo')
        ->format('Y-m-d h:i:s');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
