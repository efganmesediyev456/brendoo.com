<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TopLineResource;
use App\Models\TopLine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TopLineController extends Controller
{
    public function index() : JsonResponse
    {

        $locale   = app()->getLocale();
        $cacheKey = "topline_{$locale}";



        $top_line = Cache::remember($cacheKey, 3600, function () use ($locale) {
            return \DB::table('top_lines as w')
                ->leftJoin('top_line_translations as t', function ($join) use ($locale) {
                    $join->on('t.top_line_id', '=', 'w.id')
                        ->where('t.locale', '=', $locale);
                })
                ->select('w.id', 't.title')->first();
        });
        

        if(!$top_line){
            return response()->json([
                'data' => null,
                'top_line' => false
            ]);
        }

        return response()->json([
            'data' => [
                'id'=>$top_line->id,
                'title'=>$top_line->title
            ],
            'top_line' => true
        ]);

    }
}
