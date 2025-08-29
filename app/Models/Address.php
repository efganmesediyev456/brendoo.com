<?php

namespace App\Models;

use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{

    use HasFactory, SoftDeletes,LogsActivityTrait;
    protected $guarded = [];


    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'regionId');
    }
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id','cityId');
    }

}
