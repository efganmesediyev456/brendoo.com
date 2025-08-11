<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopDeliveryStatus extends Model
{
    protected $fillable = [
        'status_id', 
        'title_en', 
        'title_ru'
    ];

    // Cast status_id to array
    protected $casts = [
        'status_id' => 'array'
    ];

    public $table = 'topdelivery_statuses';


    public function getTitleAttribute(){
        $locale = app()->getLocale();
        $title = 'title_'.$locale;
        return $this->$title;
    }
}
