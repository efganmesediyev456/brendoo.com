<?php

namespace App\Models;

use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Image extends Model
{

    use HasFactory,SoftDeletes,LogsActivityTrait;
    protected $guarded = [];


     protected static function booted()
    {
        static::saved(function ($brand) {
            Cache::flush();
        });

        static::deleted(function ($brand) {
            Cache::flush();
        });
    }

}
