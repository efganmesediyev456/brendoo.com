<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OnBoardingResource;
use App\Models\OnBoarding;
use Illuminate\Http\Request;

class OnBoardingController extends Controller
{

    public function index()
    {
        $on_boarding = OnBoarding::query()->active()->get();
        return response()->json(OnBoardingResource::collection($on_boarding));
    }
}
