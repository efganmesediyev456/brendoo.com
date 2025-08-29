<?php

namespace App\Http\Resources;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileBannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $base_url = url('/');

        $logo = @Brand::find($this->filter_conditions['brand_id'])->image;
        
        

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'image' => $base_url . '/storage/' . $this->image,
            'logo' => $base_url . '/storage/' . $logo,
            'filter_conditions' => $this->filter_conditions,
            
        ];
    }
}
