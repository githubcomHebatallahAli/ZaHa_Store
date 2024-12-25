<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use App\Http\Resources\ImageResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserRegisterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'email'=>$this->email,
            'phoNum' => $this -> phoNum ,
            'governorate' => $this -> governorate,
            'address' => $this -> address,
            'image' => new ImageResource($this->whenLoaded('image')),
        ];
    }
}
