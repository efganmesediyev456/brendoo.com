<?php

namespace App\Http\Controllers\Admin\Influencer;

use App\Http\Controllers\Controller;
use App\Http\Resources\InfluencerResource;
use App\Models\Collection;
use App\Models\Influencer;
use App\Services\Influencer\MainService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class InfluencerCollectionController extends Controller{

    public  MainService $mainService;
    public function __construct(){
        $this->mainService = new MainService;
        $this->mainService->model = new Influencer;
        $this->middleware('permission:list-collections|create-collections|edit-collections', ['only' => ['index','show']]);
        $this->middleware('permission:create-collections', ['only' => ['create','store']]);
        $this->middleware('permission:edit-collections', ['only' => ['edit']]);
        $this->middleware('permission:changeStatus-collections', ['only' => ['changeStatus']]);
    }
    public function index(Influencer $influencer){
        $collections = $influencer->collections()->orderBy('id','desc')->paginate(10);
        return view('influencers.collections.index', compact('collections', 'influencer'));
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
        "earn_price"=>$request->earn_price
       ]);
       return redirect()->back()->withSuccess("Kolleksiya uğurla redaktə edildi!");
    }


    public function delete(Request $request, Influencer $influencer, Collection $collection){
       $collection->delete();
       return redirect()->back()->withSuccess("Kolleksiya uğurla silindi!");
    }




}