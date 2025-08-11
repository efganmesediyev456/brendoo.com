<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class StoryTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = ['title', 'description'];
}