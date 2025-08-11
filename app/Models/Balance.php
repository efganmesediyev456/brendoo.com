<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Balance extends Model{
       public $guarded = [];

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

