<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HomeProductResource;
use App\Http\Resources\CategoryFilterResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SearchProductsResource;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Category;
use App\Models\RecentlyViewedProduct;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;


class ProductController extends Controller
{
    // public function index(Request $request)
    // {

    //     $query = Product::query()
    //         ->where('is_active', true);


    //     if (!$request->has('search')) {
    //         $query->with(['filters.options', 'category', 'sub_category', 'sliders', 'brand', 'translations']);
    //     }

    //     if ($request->has('category_id')) {
    //         $query->where('category_id', $request->category_id);
    //     }

    //     if ($request->has('sub_category_id')) {
    //         $query->where('sub_category_id', $request->sub_category_id);
    //     }

    //     if ($request->has('third_category_id')) {
    //         $query->where('third_category_id', $request->third_category_id);
    //     }

    //     if ($request->has('min_price') && $request->has('max_price')) {
    //         $query->whereBetween('price', [$request->min_price, $request->max_price]);
    //     }

    //     if ($request->filled('collection_id')) {
    //         $collection = Collection::find($request->collection_id);
    //         $productCollections = $collection?->products?->pluck('id')?->toArray();
    //         if (count($productCollections)) {
    //             $query->whereIn("id", $productCollections);
    //         }
    //     }

    //     if ($request->has('brand_id')) {
    //         $brandIds = $request->brand_id;

    //         if (!is_array($brandIds)) {
    //             $brandIds = [$brandIds];
    //         }

    //         $query->whereIn('brand_id', $brandIds);
    //     }


    //     if ($request->filled('discount') && $request->boolean('discount') === true) {
    //         $query->where(function ($q) {
    //             $q->whereNotNull('discount')->where('discount', '!=', 0);
    //         });
    //     }

    //     if ($request->filled('is_season') && $request->boolean('is_season')) {
    //         $query->where('is_season', true);
    //     }

    //     if ($request->filled('is_popular') && $request->boolean('is_popular')) {
    //         $query->where('is_popular', true);
    //     }



    //     $optionIds = $request->option_ids;
    //     if ($optionIds) {
    //         $query->whereHas('options', function ($query) use ($optionIds) {
    //             $query->whereIn('options.id', $optionIds);
    //         });
    //     }
    //     if ($request->has('sort')) {
    //         switch ($request->sort) {
    //             case 'A-Z':
    //                 $query->orderByTranslation('title', 'ASC');
    //                 break;
    //             case 'Z-A':
    //                 $query->orderByTranslation('title', 'DESC');
    //                 break;
    //             case 'expensive-cheap':
    //                 $query->orderBy('price', 'desc');
    //                 break;
    //             case 'cheap-expensive':
    //                 $query->orderBy('price', 'asc');
    //                 break;
    //             case 'old-new':
    //                 $query->orderBy('created_at', 'asc');
    //                 break;
    //             case 'new-old':
    //                 $query->orderBy('created_at', 'desc');
    //                 break;
    //             default:
    //                 break;
    //         }
    //     }
    //     $products = null;
    //     if ($request->has('search')) {

    //         $query->whereHas('translations', function ($q) use ($request) {
    //             return $q->where('locale', app()->getLocale())->where('title', 'like', '%' . $request->search . '%');
    //         });
    //         $products = $query->inRandomOrder()->limit(100)->get();
    //         $data = $products->map(function ($item) {
    //             return [
    //                 'id' => $item->id,
    //                 'title' => $item->title,
    //                 'slug' =>
    //                     [
    //                         'en' => $item->translate('en')->slug,
    //                         'ru' => $item->translate('ru')->slug,
    //                     ],
    //                 'price' => $item->price,
    //                 'discount' => $item->discount,
    //                 'discounted_price' => $item->discounted_price,
    //                 'image' => $item->image,
    //             ];
    //         });
    //         return response()->json([
    //             'data' => $data,
    //         ]);
    //     } else {
    //         $products = $query->inRandomOrder()->paginate(12);

    //     }
    //     return HomeProductResource::collection($products);
    // }

    public function index(Request $request)
    {
        $query = Product::query()->where('is_active', true);

        if ($request->has('search')) {
            $locale = app()->getLocale();
            $search = $request->search;

            $cacheKey = "search:{$locale}:{$search}";

            $query->with('translations');

            $data = Cache::remember($cacheKey, 600, function () use ($query, $locale, $search) {
                $query->whereHas('translations', function ($q) use ($locale, $search) {
                    return $q->where('locale', $locale)
                        ->where('title', 'like', '%' . $search . '%');
                });

                $products = $query
                    ->select('id', 'price', 'discount', 'discounted_price', 'image')
                    ->with(['translations:id,product_id,locale,slug,title'])
                    ->inRandomOrder()
                    ->limit(100)
                    ->get();

                return $products->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'slug' => [
                            'en' => optional($item->translate('en'))->slug,
                            'ru' => optional($item->translate('ru'))->slug,
                        ],
                        'price' => $item->price,
                        'discount' => $item->discount,
                        'discounted_price' => $item->discounted_price,
                        'image' => $item->image,
                    ];
                });
            });

            return response()->json([
                'data' => $data,
            ]);
        }

        $filters = collect([]);

        if (!$request->has('search')) {
            $query->with(['filters.options', 'category', 'sub_category', 'sliders', 'brand', 'translations']);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
            $id = $request->category_id;
            $locale = app()->getLocale();
            $ttl = 60 * 60; // 1 saat
            $key = "cat:{$id}:filters:{$locale}:v1";

            // $payload = Cache::remember($key, $ttl, function () use ($id) {
            $payload=    $category = Category::query()
                    ->select('id', 'image') // lazım olan sütunlar
                    ->with([
                        // SubCategories və üçüncü səviyyə
                        'subCategories:id,category_id',
                        'subCategories.third_categories:id,sub_category_id',

                        // Yalnız bu kateqoriyada istifadə olunan option-ları olan filterlər
                        'filters' => function ($q) use ($id) {
                            $q->whereExists(function ($sub) use ($id) {
                                $sub->select(DB::raw(1))
                                    ->from('options')
                                    ->join('product_filter_options', 'product_filter_options.option_id', '=', 'options.id')
                                    ->join('products', 'products.id', '=', 'product_filter_options.product_id')
                                    // exists-i filters cədvəlinə bağlayırıq
                                    ->whereColumn('options.filter_id', 'filters.id')
                                    ->where('products.category_id', $id)
                                    ->where('products.is_active', true)
                                    ->where('options.is_active', true);
                            })
                                ->with([
                                    'options' => function ($oq) use ($id) {
                                        $oq->select('options.id', 'options.filter_id', 'options.color_code')
                                            ->where('options.is_active', true)
                                            ->whereExists(function ($sub) use ($id) {
                                                $sub->select(DB::raw(1))
                                                    ->from('product_filter_options')
                                                    ->join('products', 'products.id', '=', 'product_filter_options.product_id')
                                                    ->whereColumn('product_filter_options.option_id', 'options.id')
                                                    ->where('products.category_id', $id)
                                                    ->where('products.is_active', true);
                                            })
                                            ->orderBy('options.id');
                                    }
                                ]);
                        },
                    ])
                    ->findOrFail($id);

                $category = (new CategoryFilterResource($category))->resolve();
            // });

            $filters = $payload['filters'];

        }

        if ($request->filled('sub_category_id')) {
            $query->where('sub_category_id', $request->sub_category_id);
        }

        if ($request->filled('third_category_id')) {
            $query->where('third_category_id', $request->third_category_id);
        }

        if ($request->filled('min_price') && $request->filled('max_price')) {
            $query->whereBetween('price', [$request->min_price, $request->max_price]);
        }

        // if ($request->filled('collection_id')) {
        //     $query->whereExists(function ($q) use ($request) {
        //         $q->select(DB::raw(1))
        //             ->from('collection_product')
        //             ->whereColumn('collection_product.product_id', 'products.id')
        //             ->where('collection_product.collection_id', $request->collection_id);
        //     });
        // }

        if ($request->filled('collection_id')) {
            $collection = Collection::find($request->collection_id);
            $productCollections = $collection?->products?->pluck('id')?->toArray();
            if (count($productCollections)) {
                $query->whereIn("id", $productCollections);
            }
        }

        if ($request->filled('brand_id')) {
            $brandIds = (array) $request->brand_id;
            $query->whereIn('brand_id', $brandIds);
        }

        if ($request->boolean('discount')) {
            $query->where('discount', '>', 0);
        }

        if ($request->boolean('is_season')) {
            $query->where('is_season', true);
        }

        if ($request->boolean('is_popular')) {
            $query->where('is_popular', true);
        }

        if ($optionIds = $request->option_ids) {
            $query->whereExists(function ($q) use ($optionIds) {
                $q->select(DB::raw(1))
                    ->from('option_product')
                    ->whereColumn('option_product.product_id', 'products.id')
                    ->whereIn('option_product.option_id', $optionIds);
            });
        }

        if ($request->filled('sort')) {
            match ($request->sort) {
                'A-Z' => $query->orderByTranslation('title', 'ASC'),
                'Z-A' => $query->orderByTranslation('title', 'DESC'),
                'expensive-cheap' => $query->orderBy('price', 'desc'),
                'cheap-expensive' => $query->orderBy('price', 'asc'),
                'old-new' => $query->orderBy('created_at', 'asc'),
                'new-old' => $query->orderBy('created_at', 'desc'),
                default => null
            };
        }

        if ($request->has('search')) {
            $query->whereHas('translations', function ($q) use ($request) {
                $q->where('locale', app()->getLocale())
                    ->where('title', 'like', '%' . $request->search . '%');
            });
            $products = $query->paginate(100);
        } else {
            $products = $query
                ->take(100)
                ->inRandomOrder()
                ->paginate(24);
        }
        // $data = $products->map(fn($item) => [
        //     'id' => $item->id,
        //     'title' => $item->title,
        //     'slug' => [
        //         'en' => $item->translate('en')->slug,
        //         'ru' => $item->translate('ru')->slug,
        //     ],
        //     'price' => $item->price,
        //     'discount' => $item->discount,
        //     'discounted_price' => $item->discounted_price,
        //     'image' => $item->image,
        // ]);
        // return response()->json(['data' => $data]);
        // }

        return HomeProductResource::collection($products);
    }


    //     public function index(Request $request)
// {
//     $query = Product::query()->where('is_active', true);

    //     if (!$request->has('search')) {
//         $query->with(['filters.options', 'category', 'sub_category', 'sliders', 'brand', 'translations']);
//     }

    //     if ($request->filled('category_id')) {
//         $query->where('category_id', $request->category_id);
//     }

    //     if ($request->filled('sub_category_id')) {
//         $query->where('sub_category_id', $request->sub_category_id);
//     }

    //     if ($request->filled('third_category_id')) {
//         $query->where('third_category_id', $request->third_category_id);
//     }

    //     if ($request->filled('min_price') && $request->filled('max_price')) {
//         $query->whereBetween('price', [$request->min_price, $request->max_price]);
//     }

    //     if ($request->filled('collection_id')) {
//         $query->whereExists(function ($q) use ($request) {
//             $q->select(DB::raw(1))
//               ->from('collection_product')
//               ->whereColumn('collection_product.product_id', 'products.id')
//               ->where('collection_product.collection_id', $request->collection_id);
//         });
//     }

    //     if ($request->filled('brand_id')) {
//         $brandIds = (array) $request->brand_id;
//         $query->whereIn('brand_id', $brandIds);
//     }

    //     if ($request->boolean('discount')) {
//         $query->where('discount', '>', 0);
//     }

    //     if ($request->boolean('is_season')) {
//         $query->where('is_season', true);
//     }

    //     if ($request->boolean('is_popular')) {
//         $query->where('is_popular', true);
//     }

    //     if ($optionIds = $request->option_ids) {
//         $query->whereExists(function ($q) use ($optionIds) {
//             $q->select(DB::raw(1))
//               ->from('option_product')
//               ->whereColumn('option_product.product_id', 'products.id')
//               ->whereIn('option_product.option_id', $optionIds);
//         });
//     }

    //     if ($request->filled('sort')) {
//         match($request->sort) {
//             'A-Z' => $query->orderByTranslation('title', 'ASC'),
//             'Z-A' => $query->orderByTranslation('title', 'DESC'),
//             'expensive-cheap' => $query->orderBy('price', 'desc'),
//             'cheap-expensive' => $query->orderBy('price', 'asc'),
//             'old-new' => $query->orderBy('created_at', 'asc'),
//             'new-old' => $query->orderBy('created_at', 'desc'),
//             default => null
//         };
//     }

    //     if ($request->has('search')) {
//         $query->whereHas('translations', function ($q) use ($request) {
//             $q->where('locale', app()->getLocale())
//               ->where('title', 'like', '%' . $request->search . '%');
//         });
//         $products = $query->limit(100)->get();
//         $data = $products->map(fn($item) => [
//             'id' => $item->id,
//             'title' => $item->title,
//             'slug' => [
//                 'en' => $item->translate('en')->slug,
//                 'ru' => $item->translate('ru')->slug,
//             ],
//             'price' => $item->price,
//             'discount' => $item->discount,
//             'discounted_price' => $item->discounted_price,
//             'image' => $item->image,
//         ]);
//         return response()->json(['data' => $data]);
//     }

    //     return HomeProductResource::collection($query->paginate(36));
// }








    public function collection(Request $request, $collectionSlug)
    {
        $collection = Collection::whereSlug($collectionSlug)->first();
        if (!$collection->status) {
            throw new Exception("Kolleksiya hələ təsdiqlənməyib.");
        }
        $products = $collection?->products();
        $products = $products->inRandomOrder()->paginate(100);

        if ($products) {
            return HomeProductResource::collection($products);
        }
        return [

        ];
    }

    public function search(Request $request)
    {
        $query = DB::table('products')
            ->join('product_translations', 'products.id', '=', 'product_translations.product_id')
            ->where('products.is_active', true)
            ->where('product_translations.locale', app()->getLocale())
            ->select('products.id', 'products.price', 'products.image', 'product_translations.slug', 'product_translations.title')
            ->orderByDesc('products.id');

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;

            $query->whereRaw('SOUNDEX(product_translations.title) = SOUNDEX(?)', [$search]);
        }

        $products = $query->paginate(20);

        return response()->json(SearchProductsResource::collection($products));
    }

    //    public function search(Request $request)
//    {
//        $query = DB::table('products')
//            ->join('product_translations', 'products.id', '=', 'product_translations.product_id')
//            ->where('products.is_active', true)
//            ->where('product_translations.locale', app()->getLocale())
//            ->select('products.id', 'products.price', 'products.image', 'product_translations.slug', 'product_translations.title')
//            ->orderByDesc('products.id');
//
//        if ($request->has('search') && $request->search !== '') {
//            $search = $request->search;
//
//            $query->whereRaw('SOUNDEX(product_translations.title) = SOUNDEX(?)', [$search]);
//        }
//
//        $products = $query->paginate(20);
//
//        return response()->json(SearchProductsResource::collection($products));
//    }



    public function productSingle($slug): JsonResponse
    {

        $product = Product::query()->with(
            [
                'comments' => function ($q) {
                    $q->with('customer')->where('is_accept', true);
                },
                'filters.options',
                'category',
                'sub_category',
                'sliders',
                'filters'
            ]
        )
            ->whereTranslation('slug', $slug)->first();

        return response()->json(new ProductResource($product));

    }

    public function product($id): JsonResponse
    {
        try {
            $product = Product::with(
                'filters.options',
                'comments.customer',
                'category',
                'sub_category',
                'sliders'
            )->findOrFail($id);

            return response()->json(new ProductResource($product));
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found.' . $e->getMessage(),
                'status' => 404
            ], 404);
        }
    }

    public function getProducts(Request $request): JsonResponse
    {
        $product_ids = $request->product_ids ?? [];
        $products = Product::query()->whereIn('id', $product_ids)->get();

        return response()->json(ProductResource::collection($products));

    }


    public function trackProductView(Request $request): JsonResponse
    {
        $productId = $request->product_id;

        if (!auth()->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userId = auth()->user()->id;

        $existingView = RecentlyViewedProduct::query()->where('customer_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if (!$existingView) {
            RecentlyViewedProduct::create([
                'customer_id' => $userId,
                'product_id' => $productId
            ]);
        }

        // Yalnız son 5 baxılan məhsulu saxla
//        RecentlyViewedProduct::query()->where('customer_id', $userId)
//            ->orderBy('created_at', 'desc')
//            ->skip(5)
//            ->take(PHP_INT_MAX)
//            ->delete();

        return response()->json(['message' => 'Viewed products updated']);
    }

    // public function getRecentlyViewedProducts(): JsonResponse
    // {
    //     if (!auth()->user()) {
    //         return response()->json(['message' => 'Unauthorized'], 401);
    //     }

    //     $userId = auth()->user()->id;

    //     $productIds = RecentlyViewedProduct::where('customer_id', $userId)
    //         ->orderBy('created_at', 'desc')
    //         ->limit(5)
    //         ->pluck('product_id');

    //     if ($productIds->isEmpty()) {
    //         return response()->json([]);
    //     }

    //     $products = Product::query()->whereIn('id', $productIds)
    //         ->orderByRaw("FIELD(id, " . implode(',', $productIds->toArray()) . ")")
    //         ->get();

    //     return response()->json(HomeProductResource::collection($products));
    // }


    public function getRecentlyViewedProducts(): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userId = auth()->guard('customers')->user()->id;
        $locale = app()->getLocale();
        $cacheKey = "recently_viewed_{$userId}_{$locale}";

        // 3 dəqiqəlik cache (istəyə görə dəyişə bilərsən)
        $products = Cache::remember($cacheKey, 3600, function () use ($userId) {
            $productIds = RecentlyViewedProduct::where('customer_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->pluck('product_id');

            if ($productIds->isEmpty()) {
                return collect(); // boş kolleksiya qaytar
            }

            return Product::query()
                ->whereIn('id', $productIds)
                ->orderByRaw("FIELD(id, " . implode(',', $productIds->toArray()) . ")")
                ->get();
        });

        if ($products->isEmpty()) {
            return response()->json([]);
        }

        return response()->json(HomeProductResource::collection($products));
    }


    public function moreProducts($id, Request $request)
    {
        try {

            $product = Product::find($id);

            $paginate = $request->perPage ?? 30;

            // dd($product->third_category_id);

            $products = Product::query()
                ->where('third_category_id', $product->third_category_id)
                ->where('id', '!=', $id)
                ->where('is_active', 1)
                ->with([
                    'comments' => function ($q) {
                        $q->with('customer:id,name')->where('is_accept', true);
                    },
                    'filters.options',
                    'category',
                    'sub_category',
                    'sliders',
                    'filters'
                ])
                ->orderByRaw('RAND()')
                ->limit($paginate)
                ->get();

            // dd($products);


            return response()->json(ProductResource::collection($products));

        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }

}
