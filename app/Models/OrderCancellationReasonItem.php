<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Astrotomic\Translatable\Translatable;



class OrderCancellationReasonItem extends Model{

    public $guarded = [];


    public function reason(){
        return $this->belongsTo(OrderCancellationReason::class, 'reason_id');
    }

}