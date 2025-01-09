<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Traits\ManagesModelsTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Resources\Admin\ProductResource;

class ProductController extends Controller
{
    use ManagesModelsTrait;
    public function showAll()
    {
        $this->authorize('manage_users');

        $Product = Product::get();

                  return response()->json([
                      'data' =>  ProductResource::collection($Product),
                      'message' => "Show All Products."
                  ]);
    }

    public function create(ProductRequest $request)
    {
        $this->authorize('manage_users');
        $formattedSellingPrice = number_format($request->sellingPrice, 2, '.', '');
        $formattedPurchesPrice = number_format($request->purchesPrice, 2, '.', '');
        $profit = $formattedSellingPrice - $formattedPurchesPrice;
           $Product =Product::create ([
                "category_id" => $request->category_id,
                "shipment_id" => $request->shipment_id,
                "name" => $request->name,
                "productNum" => $request->productNum,
                "quantity" => $request->quantity,
                "sellingPrice" => $formattedSellingPrice,
                "purchesPrice" =>  $formattedPurchesPrice,
                "profit" => $profit,
                'creationDate' => now()->timezone('Africa/Cairo')
                ->format('Y-m-d h:i:s'),
            ]);
           $Product->save();
           return response()->json([
            'data' =>new ProductResource($Product),
            'message' => "Product Created Successfully."
        ]);
        }

        public function edit(string $id)
        {
            $this->authorize('manage_users');
  $Product = Product::find($id);

            if (!$Product) {
                return response()->json([
                    'message' => "Product not found."
                ], 404);
            }

            return response()->json([
                'data' => new ProductResource($Product),
                'message' => "Edit Product By ID Successfully."
            ]);
        }

        public function update(ProductRequest $request, string $id)
        {
            $this->authorize('manage_users');
            $formattedSellingPrice = number_format($request->sellingPrice, 2, '.', '');
            $formattedPurchesPrice = number_format($request->purchesPrice, 2, '.', '');
            $profit = $formattedSellingPrice - $formattedPurchesPrice;
           $Product =Product::findOrFail($id);

           if (!$Product) {
            return response()->json([
                'message' => "Product not found."
            ], 404);
        }
           $Product->update([
                "category_id" => $request->category_id,
                "shipment_id" => $request->shipment_id,
                "name" => $request->name,
                "productNum" => $request->productNum,
                "quantity" => $request->quantity,
                "sellingPrice" => $formattedSellingPrice,
                "purchesPrice" => $formattedPurchesPrice,
                "profit" =>  $profit,
                'creationDate' => now()->timezone('Africa/Cairo')
                ->format('Y-m-d h:i:s'),
                // 'creationDate'=> $request->creationDate,
            ]);

        //    $Product->save();
           return response()->json([
            'data' =>new ProductResource($Product),
            'message' => " Update Product By Id Successfully."
        ]);
    }

    public function destroy(string $id){

    return $this->destroyModel(Product::class, ProductResource::class, $id);
    }

        public function showDeleted(){

        return $this->showDeletedModels(Product::class, ProductResource::class);
    }

    public function restore(string $id)
    {

        return $this->restoreModel(Product::class, $id);
    }

    public function forceDelete(string $id){

        return $this->forceDeleteModel(Product::class, $id);
    }
}
