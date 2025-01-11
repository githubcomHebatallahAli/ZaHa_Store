<?php

namespace App\Http\Controllers\User;

use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ProductResource;
use App\Http\Resources\User\ProductUserResource;


class HomeController extends Controller
{
    public function showAllProduct()
    {
        $Product = Product::get();

                  return response()->json([
                      'data' =>  ProductUserResource::collection($Product),
                      'message' => "Show All Products."
                  ]);
    }
}
