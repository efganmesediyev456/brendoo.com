<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;


class BrandController extends Controller
{

    public function index()
    {
        $brands = Cache::remember('brands.all', 60 * 60, function () {
            return Brand::all();
        });

        return response()->json(BrandResource::collection($brands));
    }

}
