<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BalanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            'influencer' => $this->demandPaymentBalance?->influencer?->fullName,
            'balance' => null,
            'amount' => $this->amount,
            'balance_type' => $this->type == 'collection' ? 'коллекция' : 'купон',
            'customer' => $this->customer?->fullName,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'coupon' => $this->coupon?->code,
            'collection' => $this->collection?->title,
        ];
    }
}
