<?php

namespace App\Http\Resources;

use App\Models\Filter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeProductResource extends JsonResource
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
            'title' => $this->title,
            'is_new' => $this->is_new,
            'is_season' => $this->is_season,
            'slug' =>
                [
                    'en' => $this->translate('en')->slug,
                    'ru' => $this->translate('ru')->slug,
                ],
            'price' => $this->price,
            'discount' => $this->discount,
            'sliders' => SliderResource::collection($this->sliders),
            'discounted_price' => $this->discounted_price,
            'image' =>  $this->image,
            'collection_id' => $request->collection_id
        ];
    }
}
