<?php

namespace App\Http\Controllers\Admin;

use App\Models\Shipment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShipmentRequest;
use App\Http\Resources\Admin\ShipmentResource;

class ShipmentController extends Controller
{
    public function showAll()
    {
        $this->authorize('manage_users');

        $Shipment = Shipment::get();

                  return response()->json([
                      'data' =>  ShipmentResource::collection($Shipment),
                      'message' => "Show All Shipment."
                  ]);
    }

    public function create(ShipmentRequest $request)
    {
        $this->authorize('manage_users');
           $Shipment =Shipment::create ([
                "supplierName" => $request->supplierName,
                "importer" => $request->importer,
                "place" => $request->place,
                "totalProductName" => $request->totalProductName,
                "totalPrice" => $request->totalPrice,
                "description" => $request->description,
                'creationDate' => now()->timezone('Africa/Cairo')
                ->format('Y-m-d h:i:s'),
            ]);
           $Shipment->save();
           return response()->json([
            'data' =>new ShipmentResource($Shipment),
            'message' => "Shipment Created Successfully."
        ]);
        }

        public function edit(string $id)
        {
            $this->authorize('manage_users');
  $Shipment = Shipment::find($id);


            if (!$Shipment) {
                return response()->json([
                    'message' => "Shipment not found."
                ], 404);
            }

            return response()->json([
                'data' => new ShipmentResource($Shipment),
                'message' => "Edit Shipment By ID Successfully."
            ]);
        }

        public function update(ShipmentRequest $request, string $id)
        {
            $this->authorize('manage_users');
           $Shipment =Shipment::findOrFail($id);

           if (!$Shipment) {
            return response()->json([
                'message' => "Shipment not found."
            ], 404);
        }
           $Shipment->update([
            "supplierName" => $request->supplierName,
            "importer" => $request->importer,
            "place" => $request->place,
            "totalProductName" => $request->totalProductName,
            "totalPrice" => $request->totalPrice,
            "description" => $request->description,
            'creationDate' => $request->creationDate
            ]);

           $Shipment->save();
           return response()->json([
            'data' =>new ShipmentResource($Shipment),
            'message' => " Update Shipment By Id Successfully."
        ]);
    }

    public function destroy(string $id){

    return $this->destroyModel(Shipment::class, ShipmentResource::class, $id);
    }

        public function showDeleted(){

        return $this->showDeletedModels(Shipment::class, ShipmentResource::class);
    }

    public function restore(string $id)
    {

        return $this->restoreModel(Shipment::class, $id);
    }

    public function forceDelete(string $id){

        return $this->forceDeleteModel(Shipment::class, $id);
    }
}
