<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $language = $request->header('Accept-Language');
        $copyLink = "";
        // dd($this->slug);
        if($language=='ru'){
            $copyLink = "https://brendoo.com/ru/collections/".$this->slug.'?collection_id='.$this->id;
        }else{
            $copyLink = "https://brendoo.com/en/collections/".$this->slug.'?collection_id='.$this->id;
        }
        return [
            'id' => $this->id,
            "title"=>$this->title,
            "description"=>$this->description,
            "status" => (bool)$this->status ? 'Aktiv' : 'Deaktiv',
            "created_at"=>$this->created_at->format("d.m.Y"),
            "productCount"=>$this->influencer?->products?->where('collection_id', $this->id)?->count(),
            'copyLink'=>$this->status ? $copyLink : '',
            'realStatus' => (bool)$this->status
        ];
    }
}
