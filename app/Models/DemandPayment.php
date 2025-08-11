<?php

namespace App\Models;

use App\Enums\DemandPaymentStatusEnum;
use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Astrotomic\Translatable\Translatable;


class DemandPayment extends Model
{
    public $guarded = [];

    public $table = 'demand_payment_balances';

    public function influencer(){
        return $this->belongsTo(Influencer::class);
    }

    public $casts = [
        "status"=>DemandPaymentStatusEnum::class
    ];
    public function getTypeShowAttribute(){
        return match($this->type){
            "collection"=>"Kolleksiya",
            "coupon"=>"Kupon",
            default=>"Bilinmir"
        };
    }

    


   
    public function getEarningReasonAttribute()
    {
        $locale = app()->getLocale(); 

        return match ($this->type) {
            'collection' => match ($locale) {
                'en' => 'Collection usage',
                'ru' => 'Использование коллекции',
                default => 'Kolleksiya istifadəsi'
            },
            'coupon' => match ($locale) {
                'en' => 'Promo code usage',
                'ru' => 'Использование промокода',
                default => 'Promokod istifadəsi'
            },
            default => match ($locale) {
                'en' => 'Unknown',
                'ru' => 'Неизвестно',
                default => 'Bilinmir'
            }
        };
    }





    public function orderItems(){
        return $this->hasMany(DemandPaymentBalanceOrder::class,'demand_payment_balance_id');
    }


}
