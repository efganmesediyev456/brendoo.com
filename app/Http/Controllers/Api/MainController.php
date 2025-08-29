<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MainResource;
use App\Models\Main;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MainController extends Controller
{
    public function index()
    {
        $locale = app()->getLocale(); 
        $cacheKey = "main_dataa_{$locale}"; 

        $main = Cache::remember($cacheKey, 3600, function () {
            return Main::query()->with('translations')->first();
        });

        return response()->json(new MainResource($main));
    }
}
