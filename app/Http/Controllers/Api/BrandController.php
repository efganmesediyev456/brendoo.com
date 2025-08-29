<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class BrandController extends Controller
{
    public function index(): JsonResponse
    {
        $locale = app()->getLocale();
        $cacheKey = "brands-all.{$locale}";

        $brands = Cache::remember($cacheKey, 3600, function () {
            return Brand::with('translations')->get();
        });

        return response()->json(BrandResource::collection($brands));
    }
}
