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


class InfluencerCollectionProductController extends Controller{

    public  MainService $mainService;
    public function __construct(){
        $this->mainService = new MainService;
        $this->mainService->model = new Influencer;
        $this->middleware('permission:list-collection-products|create-collection-products|edit-collection-products', ['only' => ['index','show']]);
        $this->middleware('permission:create-collection-products', ['only' => ['create','store']]);
        $this->middleware('permission:edit-collection-products', ['only' => ['edit']]);
        $this->middleware('permission:delete-collection-products', ['only' => ['delete']]);
    }
    public function index(Influencer $influencer, Collection $collection){
        $products = $collection->products()->paginate(10);
        return view('influencers.products.index', compact('products'));
    }

    

    public function edit(Request $request, Influencer $influencer, Collection $collection){
       return view('influencers.collections.edit', compact('collection'));
    }

    public function update(Request $request, Influencer $influencer, Collection $collection){
       $this->validate($request, [
        "title"=>"required"
       ]);
       $collection->update([
        "title"=>$request->title,
        "description"=>$request->description,
        "status"=>$request->status,
       ]);
       return redirect()->back()->withSuccess("Kolleksiya uğurla redaktə edildi!");
    }


    public function delete(Request $request, Influencer $influencer, Collection $collection, Product $product){
        $collection->products()->detach($product->id);
        return redirect()->back()->withSuccess("Məhsul kolleksiyadan uğurla silindi!");
    }




}