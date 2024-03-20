<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\DriverDetailResource;
use App\Http\Resources\DriverProofResource;

class DriverResource extends JsonResource
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
            'mobile' => $this->mobile,
            'fcm_id' => $this->fcm_id,
            'verification_status' => $this->verification_status,
            'current_status' => $this->current_status,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'checkin_time' => $this->checkin_time,
            'refferal_id' => $this->refferal_id,
            'image' => $this->image,
            'languages_name' => $this->languages_name,
            'accepted_rejected_date' => $this->accepted_rejected_date,
            'subscription_end_date' => $this->subscription_end_date,
            'reject_reason' => $this->reject_reason,
            'created_at' => $this->created_at,
            'driver_detail' => new DriverDetailResource($this->whenLoaded('driverDetail')),
            'driver_proof' => DriverProofResource::collection($this->whenLoaded('driverProofs')),
            'refferals' => DriverResource::collection($this->whenLoaded('refferals')),
            'refferer' => new DriverResource($this->whenLoaded('refferer')),
        ];
    }
}
