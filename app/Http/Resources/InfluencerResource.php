<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InfluencerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $base_url = url('/');
        return [
            'id' => $this->id,
            'name' =>  $this->name,
            'phone' =>  $this->phone,
            'email' =>  $this->email,
            'social_profile' =>  $this->social_profile,
        ];
    }
}
