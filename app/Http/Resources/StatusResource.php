<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=>$this->id,
            'title'=>$this->title,
            "topdelivery"=>(bool)$this->topdelivery,
            "topdelivery_id"=>$this->topdelivery_id,
        ];
    }
}
