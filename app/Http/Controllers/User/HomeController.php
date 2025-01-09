<?php

namespace App\Http\Controllers\User;

use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ProductResource;


class HomeController extends Controller
{
    public function showAll()
    {
        $this->authorize('manage_users');

        $Product = Product::get();

                  return response()->json([
                      'data' =>  ProductResource::collection($Product),
                      'message' => "Show All Products."
                  ]);
    }
}
