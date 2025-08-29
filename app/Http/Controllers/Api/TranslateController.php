<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Word;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TranslateController extends Controller
{

    // public function translates(): JsonResponse
    // {
    //     $locale = app()->getLocale();
    //     $cacheKey = 'translations_' . $locale;
    //     // $locale = 'en';

    //     // $translations = Cache::remember($cacheKey, 3600, function () use ($locale) {
    //         $words = Word::all();

    //         $data = [];
    //         foreach ($words as $word) {
    //             $key = $word->key;
    //             $data[$key] = optional($word->translate($locale))->title ?? '';
    //         }

    //     $translations = $data ;
    //     // });

    //     return response()->json($translations);
    // }


    public function translates(): JsonResponse
    {
        $locale = app()->getLocale();
        $cacheKey = "translates_new_{$locale}";

        $translations = Cache::remember($cacheKey, 3600, function () use ($locale) {
            $translations = \DB::table('words as w')
                ->leftJoin('word_translations as t', function ($join) use ($locale) {
                    $join->on('t.word_id', '=', 'w.id')
                        ->where('t.locale', '=', $locale);
                })
                ->select('w.key', 't.title')
                ->pluck('t.title', 'w.key')
                ->map(fn($title) => $title ?? '')
                ->toArray();
            return $translations;
        });


        return response()->json($translations, 200, [], JSON_UNESCAPED_UNICODE);
    }

}
