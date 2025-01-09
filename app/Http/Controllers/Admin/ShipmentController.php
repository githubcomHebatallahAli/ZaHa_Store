<?php

namespace App\Http\Controllers\Admin;

use App\Models\Shipment;
use Illuminate\Http\Request;
use App\Traits\ManagesModelsTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShipmentRequest;
use App\Http\Resources\Admin\ShipmentResource;

class ShipmentController extends Controller
{
    use ManagesModelsTrait;
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
        $formattedPrice = number_format($request->totalPrice, 2, '.', '');
           $Shipment =Shipment::create ([
                "supplierName" => $request->supplierName,
                "importer" => $request->importer,
                "place" => $request->place,
                "shipmentProductNum" => $request->shipmentProductNum,
                "totalPrice" => $formattedPrice,
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
            $formattedPrice = number_format($request->totalPrice, 2, '.', '');
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
            "shipmentProductNum" => $request->shipmentProductNum,
            "totalPrice" =>  $formattedPrice,
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
        $this->authorize('manage_users');
    $Shipments=Shipment::onlyTrashed()->get();
    return response()->json([
        'data' =>ShipmentResource::collection($Shipments),
        'message' => "Show Deleted Shipments Successfully."
    ]);
    }

    public function restore(string $id)
    {
       $this->authorize('manage_users');
    $Shipment = Shipment::withTrashed()->where('id', $id)->first();
    if (!$Shipment) {
        return response()->json([
            'message' => "Shipment not found."
        ], 404);
    }
    $Shipment->restore();
    return response()->json([
        'data' =>new ShipmentResource($Shipment),
        'message' => "Restore Shipment By Id Successfully."
    ]);
    }

    public function forceDelete(string $id){

        return $this->forceDeleteModel(Shipment::class, $id);
    }
}
