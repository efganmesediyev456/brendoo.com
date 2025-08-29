<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Translation extends Model
{
    use Translatable;

    public $translatedAttributes = ['title'];
    protected $fillable = ['key'];

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
