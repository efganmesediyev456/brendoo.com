<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Word;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TranslateController extends Controller
{

    public function translates(): JsonResponse
    {
        $locale = app()->getLocale();
        $cacheKey = 'translations_' . $locale;
        // $locale = 'en';

        // $translations = Cache::remember($cacheKey, 3600, function () use ($locale) {
            $words = Word::all();

            $data = [];
            foreach ($words as $word) {
                $key = $word->key;
                $data[$key] = optional($word->translate($locale))->title ?? '';
            }

        $translations = $data ;
        // });

        return response()->json($translations);
    }

}
