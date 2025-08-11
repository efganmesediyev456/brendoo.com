<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Http\Resources\MobileBannerResource;
use App\Models\Banner;
use App\Models\MobileBanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index() : JsonResponse
    {
        $banners = Banner::query()->active()->get();
        return response()->json(BannerResource::collection($banners));
    }

    public function mobileBanners() : JsonResponse
    {
        $banners = MobileBanner::query()->active()->get();
        return response()->json(MobileBannerResource::collection($banners));
    }
}
