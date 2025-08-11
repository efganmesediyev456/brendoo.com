<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CouponResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       
        $locale = app()->getLocale();
        return [
            'id' => $this->id,
            'title' => $this->title,
            'is_active' => (bool) $this->is_active,
            "usage_count" => $this->influencer->typeBalanceValues('coupon')->where('coupon_id', $this->id)->where('type','IN')->count(),
            "total_earnings"=>$this->influencer->typeBalanceValues('coupon')->where('coupon_id', $this->id)->where('type','IN')->sum('amount'),
            "from_date"=>$this->valid_from,
            "to_date"=>$this->valid_until,
            "status" => Carbon::parse($this->valid_until)->isFuture()
                ? [
                    'az' => 'Aktiv',
                    'ru' => 'Активен',
                    'en' => 'Active',
                ][$locale]
                : [
                    'az' => 'Müddəti bitib',
                    'ru' => 'Истёк срок действия',
                    'en' => 'Expired',
                ][$locale],
            "code" => $this->code
        ];
    }
}
