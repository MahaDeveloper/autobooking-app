<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DriverProofResource extends JsonResource
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
            'driver_id' => $this->driver_id,
            'number' => $this->number,
            'img_url' => $this->img_url,
            'type' => $this->type,
            'details' => $this->details,
            'verified' => $this->verified,
            'created_at' => $this->created_at,
        ];
    }
}
