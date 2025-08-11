<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class Status extends Model{

    public $guarded = [];


    public function paid(){
        return $this->where('is_paid_status',1)->where('deleteable',0)->first();
    }

    public function paidStatusAdmin(){
        return $this->where('is_paid_status',1)->where('deleteable',0)->where('type',0)->first();
    }

    public function orderReceived(){
        return $this->where('is_order_received_status',1)->where('deleteable',0)->where('type',0)->first();
    }
    public function paidStatusFront(){
        return $this->where('is_paid_status',1)->where('deleteable',0)->where('type',1)->first();
    }

    public function turkishOffice(){
        return $this->where('is_office_status',1)->where('deleteable',0)->where('type',1)->first();
    }

    public function turkishOfficeAdmin(){
        return $this->where('is_office_status',1)->where('deleteable',0)->where('type',0)->first();
    }

     public function boxFrontStatus(){
        return $this->where('is_box_status',1)->where('deleteable',0)->where('type',1)->first();
    }

     public function boxAdminStatus(){
        return $this->where('is_box_status',1)->where('deleteable',0)->where('type',0)->first();
    }

    public function cancelFront(){
        return $this->where('is_cancel',1)->where('deleteable',0)->where('type',1)->first();
    }

    public function cancelAdmin(){
        return $this->where('is_cancel',1)->where('deleteable',0)->where('type',0)->first();
    }

    public function returnFront(){
        return $this->where('is_return',1)->where('deleteable',0)->where('type',1)->first();
    }

    public function returnAdmin(){
        return $this->where('is_return',1)->where('deleteable',0)->where('type',0)->first();
    }

    public function russianCargoFront(){
        return $this->where('is_russian_cargo',1)->where('deleteable',0)->where('type',1)->first();
    }

    public function russianCargoAdmin(){
        return $this->where('is_russian_cargo',1)->where('deleteable',0)->where('type',0)->first();
    }



    public function related(){
        return $this->hasOne(Status::class,'related_id');
    }

    public function getTitleAttribute(){

        $locale= app()->getLocale();
      
        $title = 'title_'.$locale;
        return $this->$title;
    }


    public function getStatusViewAttribute(){
        return match($this->type){
            0=>"Admin",
            1=>"Front",
            2=>"Topdelivery",
            default=>"Bilinmir"
        };
    }
    
}