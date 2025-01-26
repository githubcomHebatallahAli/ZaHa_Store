<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Traits\ManagesModelsTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Resources\Admin\CategoryResource;

class CategoryController extends Controller
{
    use ManagesModelsTrait;
    public function showAll()
    {
        $this->authorize('manage_users');

        $category = Category::withCount('products')->paginate(10);

                  return response()->json([
                      'data' =>  CategoryResource::collection($category),
                      'pagination' => [
                        'total' => $category->total(),
                        'count' => $category->count(),
                        'per_page' => $category->perPage(),
                        'current_page' => $category->currentPage(),
                        'total_pages' => $category->lastPage(),
                        'next_page_url' => $category->nextPageUrl(),
                        'prev_page_url' => $category->previousPageUrl(),
                    ],
                      'message' => "Show All Category  With Products."
                  ]);
    }
    public function showAllCat()
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
                "status" => 'notView',
            ]);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store(Category::storageFolder);
                $Category->image = $imagePath;
            }
           $Category->save();
           return response()->json([
            'data' =>new CategoryResource($Category),
            'message' => "Category Created Successfully."
        ]);
        }

        public function edit(string $id)
        {
            $this->authorize('manage_users');
  $category = Category::withCount('products')->find($id);

            if (!$category) {
                return response()->json([
                    'message' => "Category not found."
                ], 404);
            }

            return response()->json([
                'data' => new CategoryResource($category),
                'message' => "Edit Category By ID Successfully."
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
            "status" => $request-> status,
            ]);

            if ($request->hasFile('image')) {
                if ($Category->image) {
                    Storage::disk('public')->delete( $Category->image);
                }
                $imagePath = $request->file('image')->store('Categories', 'public');
                 $Category->image = $imagePath;
            }

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

    public function view(string $id)
    {
        $this->authorize('manage_users');
        $Category =Category::findOrFail($id);

        if (!$Category) {
         return response()->json([
             'message' => "Category not found."
         ], 404);
     }

        $Category->update(['status' => 'view']);

        return response()->json([
            'data' => new CategoryResource($Category),
            'message' => 'Category has been view.'
        ]);
    }

    public function notView(string $id)
    {
        $this->authorize('manage_users');
        $Category =Category::findOrFail($id);

        if (!$Category) {
         return response()->json([
             'message' => "Category not found."
         ], 404);
     }

        $Category->update(['status' => 'notView']);

        return response()->json([
            'data' => new CategoryResource($Category),
            'message' => 'Category has been delivery.'
        ]);
    }



    }


