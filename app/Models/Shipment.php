<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'supplierName',
        'importer',
        'place',
        'totalProductNum',
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
