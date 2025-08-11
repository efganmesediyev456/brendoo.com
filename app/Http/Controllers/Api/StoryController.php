<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RuleResource;
use App\Http\Resources\StoryResource;
use App\Models\Rule;
use App\Models\Story;
use Illuminate\Http\Request;
use Auth;

class StoryController extends Controller
{
    public function index()
    {
        $influencer = Auth::guard('influencers')->user();
        return StoryResource::collection($influencer->stories);
    }

    public function story($id){
        try{
            $story = Story::find($id);
            return new StoryResource($story);
        }catch(\Exception $e){
            return $this->responseMessage('error','System error'.$e->getMessage(),null,500, null);
        }
    }
}
