<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'address' => $this->address,
            'region_id' => $this->region_id,
            'city_id' => $this->city_id,
            'additional_info' => $this->additional_info,
            'region' => $this->region?->regionName,
            'city' => $this->city?->cityName,
        ];
    }
}
