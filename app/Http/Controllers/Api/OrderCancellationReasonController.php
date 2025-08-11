<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OnBoardingResource;
use App\Http\Resources\OrderCancellationReasonResource;
use App\Models\OnBoarding;
use App\Models\OrderCancellationReason;
use Illuminate\Http\Request;

class OrderCancellationReasonController extends Controller
{

    public function index()
    {
        $items = OrderCancellationReason::get();
        return response()->json(OrderCancellationReasonResource::collection($items));
    }
}
