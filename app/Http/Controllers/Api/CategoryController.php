<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CatalogCategoryResource;
use App\Http\Resources\CategoryFilterResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\HomeCategoryResource;
use App\Http\Resources\SubCategoryResource;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use DB;


class CategoryController extends Controller
{


    public function index(): JsonResponse
    {
        $locale = app()->getLocale();
        $ttl = 60 * 60;

        $categories = Cache::remember("categories.all.{$locale}", $ttl, function () {
            return Category::with('subCategories', 'filters.options')->get();
        });

        return response()->json(CategoryResource::collection($categories));
    }


// public function index(): JsonResponse
// {
//     $locale = app()->getLocale();
//     $ttl = 60 * 60;

//     $categories = Cache::remember("categories.all.{$locale}", $ttl, function () {
//         return Category::query()
//             ->select('id', 'title', 'slug', 'image') 
//             ->with([
//                 'subCategories:id,category_id,title,slug', 
//                 'filters:id,category_id,title', 
//                 'filters.options:id,filter_id,title,color_code'
//             ])
//             ->get();
//     });

//     return response()->json(CategoryResource::collection($categories));
// }


    public function get_filters(int $id)
    {
        $locale = app()->getLocale();
        $ttl = 60 * 60; // 1 saat
        $key = "cat:{$id}:filters:{$locale}:v2";

        $payload = Cache::remember($key, $ttl, function () use ($id) {
            $category = Category::query()
                ->select('id', 'image') // lazım olan sütunlar
                ->with([
                    // SubCategories və üçüncü səviyyə
                    'subCategories:id,category_id',
                    'subCategories.third_categories:id,sub_category_id',

                    // Yalnız bu kateqoriyada istifadə olunan option-ları olan filterlər
                    // 'filters' => function ($q) use ($id) {
                    //     $q->whereExists(function ($sub) use ($id) {
                    //         $sub->select(DB::raw(1))
                    //             ->from('options')
                    //             ->join('product_filter_options', 'product_filter_options.option_id', '=', 'options.id')
                    //             ->join('products', 'products.id', '=', 'product_filter_options.product_id')
                    //             // exists-i filters cədvəlinə bağlayırıq
                    //             ->whereColumn('options.filter_id', 'filters.id')
                    //             ->where('products.category_id', $id)
                    //             ->where('products.is_active', true)
                    //             ->where('options.is_active', true);
                    //     })
                    //         ->with([
                    //             'options' => function ($oq) use ($id) {
                    //                 $oq->select('options.id', 'options.filter_id', 'options.color_code')
                    //                     ->where('options.is_active', true)
                    //                     ->whereExists(function ($sub) use ($id) {
                    //                         $sub->select(DB::raw(1))
                    //                             ->from('product_filter_options')
                    //                             ->join('products', 'products.id', '=', 'product_filter_options.product_id')
                    //                             ->whereColumn('product_filter_options.option_id', 'options.id')
                    //                             ->where('products.category_id', $id)
                    //                             ->where('products.is_active', true);
                    //                     })
                    //                     ->orderBy('options.id');
                    //             }
                    //         ]);
                    // },
                ])
                ->findOrFail($id);

            return (new CategoryFilterResource($category))->resolve();
        });

        return response()->json($payload);
    }



    public function catalog_categories(): JsonResponse
    {
        $locale = app()->getLocale();

        $categories = Cache::remember("catalog_categories.all.{$locale}", 60 * 60, function () {
            return Category::with('subCategories.third_categories')->get();
        });

        return response()->json(CatalogCategoryResource::collection($categories));
    }

    public function sub_categories(): JsonResponse
    {

        $sub_categories = SubCategory::all();
        return response()->json(SubCategoryResource::collection($sub_categories));

    }

//     public function home_categories(): JsonResponse
// {
//     $categories = Category::query()
//         ->where('is_home', true)
//         ->with(['products' => function ($query) {
//             $query->orderByDesc('created_at')->take(15);
//         }])
//         ->get();

//     return response()->json(HomeCategoryResource::collection($categories));
// }



    public function home_categories_last()
    {
        $categories = Category::with('subCategories.third_categories')->get();
        return 
            $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'title' => $category->title
                ];
            })->toArray();
    }

}
