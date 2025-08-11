<?php

namespace App\Http\Resources;

use App\Models\Filter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BasketProductResource extends JsonResource
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
            'slug' => [
                'en' => optional($this->translate('en'))->slug,
                'ru' => optional($this->translate('ru'))->slug,
            ],
            'price' => $this->price,
            'discount' => $this->discount,
            'discounted_price' => $this->discounted_price,
            'image' => $this->image,
        ];
    }
}
