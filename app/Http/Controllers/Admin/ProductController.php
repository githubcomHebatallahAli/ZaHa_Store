<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Traits\ManagesModelsTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Resources\Admin\ProductResource;
use App\Http\Resources\Admin\ShowAllProductResource;

class ProductController extends Controller
{
    use ManagesModelsTrait;
    public function showAll()
    {
        $this->authorize('manage_users');

        $Product = Product::paginate(10);

        return response()->json([
            'data' => ShowAllProductResource::collection($Product),
            'pagination' => [
                'total' => $Product->total(),
                'count' => $Product->count(),
                'per_page' => $Product->perPage(),
                'current_page' => $Product->currentPage(),
                'total_pages' => $Product->lastPage(),
                'next_page_url' => $Product->nextPageUrl(),
                'prev_page_url' => $Product->previousPageUrl(),
            ],
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
                "name" => $request->name,
                "quantity" => $request->quantity,
                "sellingPrice" => $formattedSellingPrice,
                "purchesPrice" =>  $formattedPurchesPrice,
                "profit" => $profit,
            ]);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store(Product::storageFolder);
                $Product->image = $imagePath;
            }

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
                "name" => $request->name,
                "quantity" => $request->quantity,
                "sellingPrice" => $formattedSellingPrice,
                "purchesPrice" => $formattedPurchesPrice,
                "profit" =>  $profit,

            ]);

           return response()->json([
            'data' =>new ProductResource($Product),
            'message' => " Update Product By Id Successfully."
        ]);
    }

    public function destroy(string $id){

    return $this->destroyModel(Product::class, ProductResource::class, $id);
    }

    public function showDeleted(){
        $this->authorize('manage_users');
    $Products=Product::onlyTrashed()->get();
    return response()->json([
        'data' =>ProductResource::collection($Products),
        'message' => "Show Deleted Products Successfully."
    ]);
    }

    public function restore(string $id)
    {
       $this->authorize('manage_users');
    $Product = Product::withTrashed()->where('id', $id)->first();
    if (!$Product) {
        return response()->json([
            'message' => "Product not found."
        ], 404);
    }
    $Product->restore();
    return response()->json([
        'data' =>new ProductResource($Product),
        'message' => "Restore Product By Id Successfully."
    ]);
    }

    public function forceDelete(string $id){

        return $this->forceDeleteModel(Product::class, $id);
    }
}
