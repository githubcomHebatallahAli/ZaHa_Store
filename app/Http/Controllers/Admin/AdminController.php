<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Traits\ManagesModelsTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImgRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Http\Resources\Auth\AdminRegisterResource;

class AdminController extends Controller
{
    use ManagesModelsTrait;

    public function showAll()
    {
        $this->authorize('manage_users');

        $Admins = Admin::get();
        return response()->json([
            'data' => AdminRegisterResource::collection($Admins),
            'message' => "Show All Admins Successfully."
        ]);
    }

    public function edit(string $id)
    {
        $this->authorize('manage_users');
        $Admin = Admin::find($id);

        if (!$Admin) {
            return response()->json([
                'message' => "Admin not found."
            ]);
        }

        return response()->json([
            'data' => new AdminRegisterResource($Admin),
            'message' => "Edit Admin By ID Successfully."
        ]);
    }

    public function update(UpdateAdminRequest $request, string $id)
    {
        $this->authorize('manage_users');
        $Admin = Admin::findOrFail($id);

        if ($request->filled('name')) {
            $Admin->name = $request->name;
        }

        if ($request->filled('email')) {
            $Admin->email = $request->email;
        }

        if ($request->filled('phoNum')) {
            $Admin->phoNum = $request->phoNum;
        }


        if ($request->filled('address')) {
            $Admin->address = $request->address;
        }


        $Admin->role_id = $request->role_id;
        $Admin->status = $request->status;

        $Admin->save();

        return response()->json([
            'data' => new AdminRegisterResource($Admin),
            'message' => "Update Admin By Id Successfully."
        ]);
    }


    public function notActive(string $id)
    {
        // $this->authorize('manage_users');
        $admin =Admin::findOrFail($id);

        if (!$admin) {
         return response()->json([
             'message' => "Admin not found."
         ]);
     }
        $this->authorize('notActive',$admin);

        $admin->update(['status' => 'notActive']);

        return response()->json([
            'data' => new AdminRegisterResource($admin),
            'message' => 'Admin has been Not Active.'
        ]);
    }
    public function active(string $id)
    {
        // $this->authorize('manage_users');
        $admin =Admin::findOrFail($id);

        if (!$admin) {
         return response()->json([
             'message' => "Admin not found."
         ]);
     }
        $this->authorize('active',$admin);

        $admin->update(['status' => 'active']);

        return response()->json([
            'data' => new AdminRegisterResource($admin),
            'message' => 'Admin has been Active.'
        ]);
    }

    public function adminUpdateProfilePicture(ImgRequest $request ,string $id)
{
    $Admin= auth()->guard('admin')->user();
    if ($Admin->id != $id) {
        return response()->json([
            'message' => "Unauthorized to update this profile."
        ]);
    }
    if ($request->hasFile('image')) {
        if ($Admin->img) {
            Storage::disk('public')->delete($Admin->img);
        }
        $imgPath = $request->file('image')->store('admin', 'public');
        $Admin->img = $imgPath;

    }
    $Admin->save();
        return response()->json([
            'message' => 'Profile photo updated successfully',
            'data' => new AdminRegisterResource($Admin),
        ]);
    }

    public function destroy(string $id)
    {
        return $this->destroyModel(Admin::class, AdminRegisterResource::class, $id);
    }

    public function showDeleted(){
        $this->authorize('manage_users');
    $Admins=Admin::onlyTrashed()->get();
    return response()->json([
        'data' =>AdminRegisterResource::colAdmintion($Admins),
        'message' => "Show Deleted Admin Successfully."
    ]);
    }

    public function restore(string $id)
    {
    $this->authorize('manage_users');
    $Admin = Admin::withTrashed()->where('id', $id)->first();
    if (!$Admin) {
        return response()->json([
            'message' => "Admin not found."
        ]);
    }

    $Admin->restore();
    return response()->json([
        'data' =>new AdminRegisterResource($Admin),
        'message' => "Restore Admin By Id Successfully."
    ]);
    }
    public function forceDelete(string $id)
    {
        return $this->forceDeleteModel(Admin::class, $id);
    }
}
