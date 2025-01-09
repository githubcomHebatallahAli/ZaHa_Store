<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Traits\ManagesModelsTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Resources\Admin\CategoryResource;
use App\Http\Resources\Admin\CategoryProductResource;

class CategoryController extends Controller
{
    use ManagesModelsTrait;
    public function showAll()
    {
        $this->authorize('manage_users');

        $category = Category::withCount('products')->get();

                  return response()->json([
                      'data' =>  CategoryResource::collection($category),
                      'message' => "Show All Category  With Products."
                  ]);
    }

    public function create(CategoryRequest $request)
    {
        $this->authorize('manage_users');
           $Category =Category::create ([
                "name" => $request->name,
            ]);
           $Category->save();
           return response()->json([
            'data' =>new CategoryResource($Category),
            'message' => "Category Created Successfully."
        ]);
        }

        public function edit(string $id)
        {
            $this->authorize('manage_users');
  $category = Category::with('products')
  ->withCount('products')->find($id);

            if (!$category) {
                return response()->json([
                    'message' => "Category not found."
                ], 404);
            }

            return response()->json([
                'data' => new CategoryProductResource($category),
                'message' => "Edit Category With product By ID Successfully."
            ]);
        }

        public function update(CategoryRequest $request, string $id)
        {
            $this->authorize('manage_users');
           $Category =Category::findOrFail($id);

           if (!$Category) {
            return response()->json([
                'message' => "Category not found."
            ], 404);
        }
           $Category->update([
            "name" => $request->name,
            ]);

           $Category->save();
           return response()->json([
            'data' =>new CategoryResource($Category),
            'message' => " Update Category By Id Successfully."
        ]);
    }

    public function destroy(string $id){

    return $this->destroyModel(Category::class, CategoryResource::class, $id);
    }

    public function showDeleted(){
        $this->authorize('manage_users');
    $Categorys=Category::onlyTrashed()->get();
    return response()->json([
        'data' =>CategoryResource::collection($Categorys),
        'message' => "Show Deleted Categorys Successfully."
    ]);
    }

    public function restore(string $id)
    {
       $this->authorize('manage_users');
    $Category = Category::withTrashed()->where('id', $id)->first();
    if (!$Category) {
        return response()->json([
            'message' => "Category not found."
        ], 404);
    }
    $Category->restore();
    return response()->json([
        'data' =>new CategoryResource($Category),
        'message' => "Restore Category By Id Successfully."
    ]);
    }

    public function forceDelete(string $id){

        return $this->forceDeleteModel(Category::class, $id);
    }


    }


