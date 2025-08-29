<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TiktokResource;
use App\Models\Tiktok;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TiktokController extends Controller
{
    public function index(): JsonResponse
    {
        $locale = app()->getLocale();

        $tiktoks = Cache::remember("tiktoks.all.{$locale}", 60 * 60, function () {
            return Tiktok::query()
                ->where('is_active', true)
                ->orderByDesc('id')
                ->get();
        });

        return response()->json(TiktokResource::collection($tiktoks));
    }
}
