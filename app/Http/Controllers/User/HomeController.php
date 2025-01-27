<?php

namespace App\Http\Controllers\User;

use App\Models\Product;
use App\Models\Category;
use App\Models\Newproduct;
use App\Models\Premproduct;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\ProductUserResource;
use App\Http\Resources\User\CategoryWithProductsResource;

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

    public function showAllCategory()
    {
        $Categories = Category::where('status', 'view')->get();

                  return response()->json([
                    'data' => $Categories->map(function ($Category) {
                        return [
                            'id' => $Category->id,
                            'name' => $Category->name,
                            'image' => $Category->image,
                        ];
                    }),
                      'message' => "Show All Categories."
                    ]);
    }

    public function showAllPremProduct()
    {
        $Premproducts = Premproduct::get();
        return response()->json([
            'data' => $Premproducts->map(function ($Premproduct) {
                return [
                    'id' => $Premproduct->id,
                    'image' => $Premproduct->product->image,
                    'name' => $Premproduct->product->name,
                    'priceBeforeDiscount'=>$Premproduct->product->priceBeforeDiscount,
                    'discount'=>$Premproduct->product->discount,
                    'sellingPrice' => $Premproduct->product->sellingPrice,
                ];
            }),
            'message' => "Show All Premium Products Successfully."
        ]);
    }

    public function showAllNewProduct()
    {
        $Newproducts = Newproduct::get();
        return response()->json([
            'data' => $Newproducts->map(function ($Newproduct) {
                return [
                    'id' => $Newproduct->id,
                    'image' => $Newproduct->product->image,
                    'name' => $Newproduct->product->name,
                    'priceBeforeDiscount'=>$Newproduct->product->priceBeforeDiscount,
                    'discount'=>$Newproduct->product->discount,
                    'sellingPrice' => $Newproduct->product->sellingPrice,
                ];
            }),
            'message' => "Show All New Products Successfully."
        ]);
    }

    public function editCategoryWithProducts(string $id)
    {

$category = Category::where('status', 'view')->with('products')
->withCount('products')->find($id);

        if (!$category) {
            return response()->json([
                'message' => "Category not found."
            ], 404);
        }

        return response()->json([
            'data' => new CategoryWithProductsResource($category),
            'message' => "Edit Category With products By ID Successfully."
        ]);
    }


}
