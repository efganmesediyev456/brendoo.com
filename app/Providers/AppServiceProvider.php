<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // $modelsToFlush = [
        //     \App\Models\Product::class,
        //     \App\Models\Category::class,
        //     \App\Models\Translation::class,
        //     \App\Models\TopLine::class,
        //     \App\Models\Word::class,
        //     \App\Models\Image::class,
        //     \App\Models\Brand::class,
        //     \App\Models\Main::class,
        //     \App\Models\Single::class,
        //     \App\Models\Special::class,
        //     \App\Models\Banner::class,
        // ];

        // Model::saved(function ($model) use ($modelsToFlush) {
        //     if (in_array(get_class($model), $modelsToFlush)) {
        //         Cache::flush();
        //     }
        // });

        // Model::deleted(function ($model) use ($modelsToFlush) {
        //     if (in_array(get_class($model), $modelsToFlush)) {
        //         Cache::flush();
        //     }
        // });

         
    }
}
