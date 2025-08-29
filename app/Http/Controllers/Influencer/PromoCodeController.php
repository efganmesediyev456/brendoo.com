<?php

namespace App\Http\Controllers\Influencer;

use App\Http\Controllers\Controller;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use Illuminate\Http\Request;    

class PromoCodeController extends Controller
{
    public function index(Request $request){

        $user = auth("influencers")->user();

        $language = $request->header('Accept-Language');
        if(strlen($language) != 2) {
           $language = 'ru';
        }
        app()->setLocale($language);

        $coupons= $user->coupons();
        if($request->filled("type")){
            $type = $request->type;
            switch($type){
                 case "active":
                    $coupons = $coupons->where("valid_until", ">", now());
                    break;

                case "expired":
                    $coupons = $coupons->where("valid_until", "<", now());
                    break;
            }
        }

        if($request->filled("search")){
            $search = $request->search;
            $coupons = $coupons->whereHas('translations', function($qq) use($search){
                $qq->where('title','like','%'.$search.'%');
            });
        }
        $coupons = $coupons->get();

        return CouponResource::collection($coupons);
    }
}
