<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\InfluencerResource;
use App\Models\Collection;
use App\Models\DemandPaymentBalanceOrder;
use App\Models\Influencer;
use App\Models\Product;
use App\Services\Influencer\MainService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class InfluencerCollectionEarningController extends Controller{

    public  MainService $mainService;
    public function __construct(){
        $this->mainService = new MainService;
        $this->mainService->model = new Influencer;
    }
    public function index($influencer, $collection, $product){
        $influencer = Influencer::find($influencer);
        $collection = Collection::find($collection);
        $product = Product::find($product);

        $demandPaymentBalanceOrders = 
        DemandPaymentBalanceOrder::
        where('collection_id', $collection->id)->
        whereHas('demandPaymentBalance', function($demandPaymentBalanceQuery) use($influencer){
            $demandPaymentBalanceQuery->where('influencer_id', $influencer->id);
        })->
        whereHas('orderItem', function($orderItem) use($product){
            $orderItem->where('product_id', $product->id);
        })->
    
        paginate(10);
        return view('influencers.balances.index', compact('demandPaymentBalanceOrders'));
    }
}