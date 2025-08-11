<?php

namespace App\Http\Resources;

use App\Enums\DemandPaymentStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DemandPaymentResource extends JsonResource
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
            'amount' => $this->amount,
            "earningReason"=>$this->earningReason,
            "paymentStatus"=>$this->status?->label(),
            "isPaid" => $this->status === DemandPaymentStatusEnum::Paid,
            "paymentDate" => $this->status === DemandPaymentStatusEnum::Paid ? $this->updated_at->format("d.m.Y") : null
        ];
    }
}
