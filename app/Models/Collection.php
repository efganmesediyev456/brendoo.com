<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Collection extends Model
{
    use HasFactory;
    protected $guarded = [ ];


    public function influencer(){
        return $this->belongsTo(Influencer::class);
    }




    public function products(){
        return $this->belongsToMany(Product::class,'influencer_collection_products');
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($collection) {
            $collection->slug = static::createUniqueSlug($collection->title);
        });
    }


     public static function createUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }
}
