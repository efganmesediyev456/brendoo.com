<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Http\Resources\MobileBannerResource;
use App\Models\Banner;
use App\Models\MobileBanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;


class BannerController extends Controller
{
    public function index() : JsonResponse
    {
        $locale = app()->getLocale();

        $banners = Cache::remember("banners.active.{$locale}", 60 * 60, function () {
            return Banner::query()->active()->get();
        });

        return response()->json(BannerResource::collection($banners));
    }

    public function mobileBanners() : JsonResponse
    {
        $banners = MobileBanner::query()->active()->get();
        return response()->json(MobileBannerResource::collection($banners));
    }
}
