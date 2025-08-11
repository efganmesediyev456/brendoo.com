<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Astrotomic\Translatable\Translatable;



class OrderCancellationReason extends Model{

    use SoftDeletes,Translatable;
    public $guarded = [];

    public $translatedAttributes = ['title'];


    public function getTranslationRelationKey(){
        return 'cancel_id';
    }
}