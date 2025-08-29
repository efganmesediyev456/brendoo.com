<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SeoResource;
use App\Models\Single;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;


class SeoController extends Controller
{
    public function index(): JsonResponse
    {
        $locale = app()->getLocale();
        $cacheKey = "singles_data_{$locale}";

        // Cache::flush();
        $singles = Cache::remember($cacheKey, 3600, function () {
            return Single::with('translations')->get();
        });

        return response()->json(SeoResource::collection($singles));
    }
}
