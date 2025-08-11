<?php

namespace App\Http\Controllers\Admin\Influencer;

use App\Http\Controllers\Controller;
use App\Http\Resources\InfluencerResource;
use App\Models\Collection;
use App\Models\Influencer;
use App\Models\Product;
use App\Services\Influencer\MainService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class InfluencerPromoCodeController extends Controller{

    public  MainService $mainService;
    public function __construct(){
        $this->mainService = new MainService;
        $this->mainService->model = new Influencer;
        // $this->middleware('permission:list-collection-products|create-collection-products|edit-collection-products', ['only' => ['index','show']]);
        // $this->middleware('permission:create-collection-products', ['only' => ['create','store']]);
        // $this->middleware('permission:edit-collection-products', ['only' => ['edit']]);
        // $this->middleware('permission:delete-collection-products', ['only' => ['delete']]);
    }
    public function index(Influencer $influencer){
        $promocodes = $influencer->coupons()->paginate(10);
        return view('influencers.promocodes.index', compact('promocodes'));
    }

}