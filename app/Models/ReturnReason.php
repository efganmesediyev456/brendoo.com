<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Astrotomic\Translatable\Translatable;

class ReturnReason extends Model
{
    use HasFactory, Translatable;
    protected $guarded = [];
    public $translatedAttributes = ['title'];
}
