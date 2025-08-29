<?php

namespace App\Models;

use App\Traits\LogsActivityTrait;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Single extends Model
{

    use HasFactory, Translatable, SoftDeletes,LogsActivityTrait;
    public $translatedAttributes = ['title','seo_description','seo_title','seo_keywords', 'slug'];
    protected $fillable = ['type'];

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
