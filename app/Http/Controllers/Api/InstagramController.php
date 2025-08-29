<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InstagramResource;
use App\Models\Instagram;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;


class InstagramController extends Controller
{
  public function index(): JsonResponse
  {
    $instagrams = Cache::remember('instagram_all-' . app()->getLocale(), 3600, function () {
      return Instagram::with([
        'translations',
        'products',
        'products.translations'
      ])->orderByDesc('id')->get();
    });


    return response()->json(InstagramResource::collection($instagrams));
  }
}
