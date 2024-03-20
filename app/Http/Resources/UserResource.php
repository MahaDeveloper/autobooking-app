<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserAddressResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'languages_name' => $this->languages_name,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'img_url' => $this->img_url,
            'refferal_id' => $this->refferal_id,
            'fcm_id' => $this->fcm_id,
            'primary_contact' => $this->primary_contact,
            'upi_id' => $this->upi_id,
            'till_ride_amount' => $this->till_ride_amount,
            'created_at' => $this->created_at,
            'status' => $this->status,
            'user_address' => UserAddressResource::collection($this->whenLoaded('userAddresses')),
            'user_emergency_contacts' => UserEmergencyContactResource::collection($this->whenLoaded('userEmergencyContacts')),
            'refferals' => UserResource::collection($this->whenLoaded('refferals')),
            'refferer' => new UserResource($this->whenLoaded('refferer')),
        ];
    }
}
