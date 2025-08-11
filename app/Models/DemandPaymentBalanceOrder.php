<?php

namespace App\Models;

use App\Enums\DemandPaymentStatusEnum;
use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Astrotomic\Translatable\Translatable;


class DemandPaymentBalanceOrder extends Model
{
    public $table = 'demand_payment_balance_orders';

    public $guarded = [];


    public function orderItem(){
        return $this->belongsTo(OrderItem::class,'order_item_id');
    }


    public function demandPaymentBalance(){
        return $this->belongsTo(DemandPayment::class);
    }

    public function influencer(){
        return $this->belongsTo(Influencer::class);
    }

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function coupon(){
        return $this->belongsTo(Coupon::class);
    }

    public function collection(){
        return $this->belongsTo(Collection::class);
    }
}
