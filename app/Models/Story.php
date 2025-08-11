<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use Translatable;

    public $translatedAttributes = ['title', 'description'];
    protected $fillable = [
        'influencer_id', 
        'image', 
        'video'
    ];

    public function influencer()
    {
        return $this->belongsTo(Influencer::class);
    }


    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function images()
    {
        return $this->hasMany(Media::class)->where('type', 'image');
    }

    public function videos()
    {
        return $this->hasMany(Media::class)->where('type', 'video');
    }
}