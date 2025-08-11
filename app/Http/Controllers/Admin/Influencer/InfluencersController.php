<?php

namespace App\Http\Controllers\Admin\Influencer;

use App\Http\Controllers\Controller;
use App\Http\Resources\InfluencerResource;
use App\Models\Influencer;
use App\Services\Influencer\MainService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class InfluencersController extends Controller{


    
    public  MainService $mainService;
    public function __construct(){
        $this->mainService = new MainService;
        $this->mainService->model = new Influencer;
        $this->middleware('permission:list-influencers|create-influencers|edit-influencers', ['only' => ['index','show']]);
        $this->middleware('permission:create-influencers', ['only' => ['create','store']]);
        $this->middleware('permission:edit-influencers', ['only' => ['edit']]);
        $this->middleware('permission:changeStatus-influencers', ['only' => ['changeStatus']]);
    }
    public function index(){
        $influencers = Influencer::orderBy('id','desc')->paginate(12);
        return view('influencers.index', compact('influencers'));
    }

    public function  changeStatus(Request $request){
       try{
            $influencer= $this->mainService->find((int)$request->id);

            if(! $influencer){
                throw new \Exception('Influencer doesnt find');
            }
            $this->mainService->update($influencer->id, [
               "status"=>$request->status 
            ]);
            return response()->json([
                "message"=>"Successfully Change Status",
                "data"=>new InfluencerResource($influencer),
            ], Response::HTTP_OK);

        }catch(\Exception $e){
            return response()->json([
                "message"=>$e->getMessage(),
                "error"=>$e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}