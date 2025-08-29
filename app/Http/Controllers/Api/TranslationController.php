<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class TranslationController extends Controller
{
    public function index()
    {

        $locale = request()->header('Accept-Language') ?? 'ru'; 

        $grouped = Cache::remember("translations_new_{$locale}", now()->addHours(1), function () use ($locale) {
            $translations = Translation::with([
                'translations' => function ($query) use ($locale) {
                    $query->where('locale', $locale);
                }
            ])->get()->mapWithKeys(function ($item) use ($locale) {
                return [$item->key => $item->translate($locale)?->title];
            });

            $grouped = [];
            foreach ($translations as $key => $value) {
                Arr::set($grouped, $key, $value);
            }

             return $translations;
        });

        return response()->json($grouped);

    }
}
