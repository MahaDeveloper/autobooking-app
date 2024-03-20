<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupportResource extends JsonResource
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
            'supportable_id' => $this->supportable_id,
            'supportable_type' => $this->supportable_type,
            'description' => $this->description,
            'reply_message' => $this->reply_msg,
            'ride_id' => $this->ride_id,
            'status' => $this->status,
            'supportable' => $this->whenLoaded('supportable'),
            'created_at' => $this->created_at,
        ];
    }
}
