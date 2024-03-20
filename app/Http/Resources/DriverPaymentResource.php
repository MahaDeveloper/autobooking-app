<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\DriverResource;

class DriverPaymentResource extends JsonResource
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
            'transaction_id' => $this->transaction_id,
            'ride_ids' => $this->ride_ids,
            'amount' => $this->amount,
            'status' => $this->status,
            'driver' => $this->whenLoaded('driver'),
        ];
    }
}
