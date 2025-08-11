<?php

namespace App\Models;

use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Astrotomic\Translatable\Translatable;


class Coupon extends Model
{
    use HasFactory, LogsActivityTrait, Translatable;

     public $translatedAttributes = [
        'title'
    ];

    protected $fillable = [
        'code', 'discount', 'type', 'valid_from', 'valid_until','is_active', 'coupon_type','earn_price','influencer_id'
    ];

    // Kuponun istifadə edildiyi müştəriləri əlaqələndiririk
    public function customers() : BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'coupon_customer');
    }

    // Kuponun istifadə edildiyi sifarişləri əlaqələndiririk
    public function orders() : BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'coupon_order');
    }

  

    // Kuponun keçərli olub-olmamasını yoxlamaq
    public function isValid()
    {
        $currentDate = now();
        return $currentDate->between($this->valid_from, $this->valid_until);
    }


    public function influencer(){
        return $this->belongsTo(Influencer::class,'influencer_id');
    }
}
