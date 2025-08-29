<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use App\Jobs\ProcessProductImport;
use App\Jobs\ProcessSingleProduct;
use App\Jobs\ZaraXmlImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\SubCategory;
use App\Models\ThirdCategory;
use App\Models\User;
use App\Services\ImageUploadService;
use App\Services\PriceCalculatorService;
use App\Services\ProductService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function __construct(
        protected ImageUploadService $imageUploadService,
        protected ProductService $productService,
        protected PriceCalculatorService $calculatorService
    ) {
        $this->middleware('permission:list-products|create-products|edit-products|delete-products', ['only' => ['index', 'show']]);
        $this->middleware('permission:create-products', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-products', ['only' => ['edit']]);
        $this->middleware('permission:delete-products', ['only' => ['destroy']]);
        $this->middleware('permission:increase-prices-products', ['only' => ['increasePrices']]);
        $this->middleware('permission:apply-discount-products', ['only' => ['applyDiscount']]);
    }

    public function generateUniqueSlug($title, $itemId = null)
    {
        $slug = Str::slug($title);

        if ($itemId) {
            $count = Product::query()->whereNot('id', $itemId)->whereTranslation('title', $title)->count();
        } else {
            $count = Product::query()->whereTranslation('title', $title)->count();
        }

        if ($count > 0) {
            $slug .= '-' . $count;
        }

        return $slug;
    }

    public function index(Request $request)
    {
        $limit = $request->input('limit', 15);

        $products = $this->productService
            ->filterTitle($request->title)
            ->filterCode($request->code)
            ->filterIsActive($request->is_active)
            ->filterLowStock($request->stock)
            ->filterCategory($request->category)
            ->filterSubcategory($request->subcategory)
            ->filterBrand($request->brand)
            ->filterUser($request->user_id)
            // ->filterStartDate($request->start_act)
            // ->filterEndDate($request->end_act)
            ->getQuery()
            ->orderByDesc('id');

           
            if ($request->filled('start_act') && $request->filled('end_act')) {
                $start = $request->input('start_act');
                $end = $request->input('end_act');
                $products = $products->whereHas('logs', function($q) use ($start, $end) {
                    $q->whereBetween('created_at', [$start, $end]);
                });
            } elseif ($request->filled('start_act')) {
                $start = $request->input('start_act');
                $products = $products->whereHas('logs', function($q) use ($start) {
                    $q->where('created_at', '>=', $start);
                });
            } elseif ($request->filled('end_act')) {
                $end = $request->input('end_act');
                $products = $products->whereHas('logs', function($q) use ($end) {
                    $q->where('created_at', '<=', $end);
                });
            }



          $products= $products->paginate($limit)
            ->withQueryString();

        $categories = Category::all();
        $subcategories = SubCategory::all();
        $brands = Brand::all();
        $users = User::all();

        return view('admin.products.index', compact(
            'products',
            'categories',
            'subcategories',
            'brands', 'users'
        ));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories    = Category::all();
        $subCategories = SubCategory::all();
        $brands        = Brand::all();

        return view('admin.products.create', compact('categories', 'subCategories', 'brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id'       => 'required|exists:categories,id',
            'sub_category_id'   => 'required|exists:sub_categories,id',
            'third_category_id' => 'nullable|exists:third_categories,id',
            'en_title'          => 'required',
            'ru_title'          => 'required',
            'en_description'    => 'nullable',
            'ru_description'    => 'nullable',
            'image'             => 'required|image',
            'size_image'        => 'nullable',
            'price'             => 'required|numeric',
            'cost_price'        => 'nullable|numeric',
            'stock'             => 'nullable|integer',
            'brand_id'          => 'nullable',
            'en_short_title'    => 'nullable',
            'ru_short_title'    => 'nullable',
        ]);

        DB::beginTransaction();
        try {
            if ($request->hasFile('image')) {
                $filename = $this->imageUploadService->upload($request->file('image'), true);
            }

            if ($request->hasFile('size_image')) {
                $filename_size_image = $this->imageUploadService->upload($request->file('size_image'), true);
            }

            $product = Product::create([
                'category_id'       => $request->category_id,
                'brand_id'          => $request->brand_id,
                'sub_category_id'   => $request->sub_category_id,
                'third_category_id' => $request->third_category_id,
                'user_id'           => auth()->id(),
                'image'             => $filename            ?? null,
                'size_image'        => $filename_size_image ?? null,
                'video'             => $request->video,
                'unit'              => $request->unit,
                'cost_price'        => $request->cost_price,
                'price'             => $request->price,
                'discount'          => $request->discount,
                'discounted_price'  => $request->discounted_price,
                'is_new'            => $request->has('is_new'),
                'is_active'         => $request->has('is_active'),
                'is_stock'          => $request->has('is_stock'),
                'is_return'         => $request->has('is_return'),
                'is_season'         => $request->has('is_season'),
                'is_popular'        => $request->has('is_popular'),
                'code'              => $request->code,
                'stock'             => $request->stock,
                'status'            => $request->status,
            ]);

            $product->translations()->createMany([
                [
                    'locale'           => 'en',
                    'title'            => $request->en_title,
                    'short_title'      => $request->en_short_title,
                    'description'      => $request->en_description,
                    'img_alt'          => $request->en_img_alt,
                    'img_title'        => $request->en_img_title,
                    'slug'             => $this->generateUniqueSlug($request->en_title),
                    'meta_title'       => $request->en_meta_title,
                    'meta_description' => $request->en_meta_description,
                    'meta_keywords'    => $request->en_meta_keywords,
                ],
                [
                    'locale'           => 'ru',
                    'title'            => $request->ru_title,
                    'short_title'      => $request->ru_short_title,
                    'description'      => $request->ru_description,
                    'img_alt'          => $request->ru_img_alt,
                    'img_title'        => $request->ru_img_title,
                    'slug'             => $this->generateUniqueSlug($request->ru_title),
                    'meta_title'       => $request->ru_meta_title,
                    'meta_description' => $request->ru_meta_description,
                    'meta_keywords'    => $request->ru_meta_keywords,
                ],
            ]);

            if ($request->hasFile('slider_images')) {
                foreach ($request->file('slider_images') as $file) {
                    $filename = $this->imageUploadService->upload($file, true);
                    $product->sliders()->create([
                        'image' => $filename,
                    ]);
                }
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();

            return $exception->getMessage();
        }

        return redirect()->route('products.edit', $product->id)->with('message', 'Product added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): void {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {

        $brands          = Brand::all();
        $categories      = Category::query()->with('filters.options')->get();
        $subCategories   = SubCategory::all();
        $thirdCategories = ThirdCategory::all();
        $selectedOptions = [];
        $defaultOptions  = [];
        $stockOptions    = [];

        foreach ($product->filters as $filter) {
            $selectedOptions[$filter->id] = $filter->pivot->pluck('option_id')->toArray();
            $defaultOptions[$filter->id]  = $filter->pivot->where('is_default', true)->first()->option_id ?? null;
            if ( ! empty($filter->pivot) && is_iterable($filter->pivot)) {
                foreach ($filter->pivot as $pivot) {
                    if ( ! empty($pivot) && is_object($pivot)) {
                        $stockOptions[$filter->id][$pivot->option_id] = $pivot->is_stock ?? false;
                    }
                }
            }
        }

        return view('admin.products.edit', compact(
            'product',
            'categories',
            'subCategories',
            'brands',
            'selectedOptions',
            'defaultOptions',
            'thirdCategories',
            'stockOptions'
        ));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {

        $request->validate([
            'category_id'       => 'required|exists:categories,id',
            'sub_category_id'   => 'nullable|exists:sub_categories,id',
            'third_category_id' => 'nullable|exists:third_categories,id',
            'en_title'          => 'required',
            'ru_title'          => 'required',
            'en_description'    => 'nullable',
            'ru_description'    => 'nullable',
            'price'             => 'nullable|numeric',
            'cost_price'        => 'nullable|numeric',
            'stock'             => 'nullable|integer',
            'brand_id'          => 'nullable',
        ]);


        DB::beginTransaction();

        try {
            if ($request->hasFile('image')) {
                $product->image = $this->imageUploadService->upload($request->file('image'), true);
            }
            if ($request->hasFile('size_image')) {
                $product->size_image = $this->imageUploadService->upload($request->file('size_image'), true);
            }
            $wasInactive = !$product->is_active;
            $isActiveNow = $request->has('is_active');
            $product->update([
                'category_id'       => $request->category_id,
                'brand_id'          => $request->brand_id,
                'sub_category_id'   => $request->sub_category_id,
                'third_category_id' => $request->third_category_id,
                'user_id'           => auth()->id(),
                'video'             => $request->video,
                'unit'              => $request->unit,
                'cost_price'        => $request->cost_price,
                'price'             => $request->price,
                'tr_price'          => $request->tr_price,
                'discount'          => $request->discount,
                'discounted_price'  => $request->discounted_price,
                'is_new'            => $request->has('is_new'),
                'is_active'         => $request->has('is_active'),
                'is_stock'          => $request->has('is_stock'),
                'is_accept'         => $request->has('is_accept'),
                'is_return'         => $request->has('is_return'),
                'is_season'         => $request->has('is_season'),
                'is_popular'        => $request->has('is_popular'),
                'code'              => $request->code,
                'stock'             => $request->stock,
                'status'            => $request->status,
            ]);

//            if ($wasInactive && $isActiveNow && is_null($product->activation_date)) {
//                $product->activation_date = now();
//                $product->save();
//            }

            $product->translations()->updateOrCreate(['locale' => 'en'], [
                'title'            => $request->en_title,
                'short_title'      => $request->en_short_title,
                'description'      => $request->en_description,
                'img_alt'          => $request->en_img_alt,
                'img_title'        => $request->en_img_title,
                'slug'             => $this->generateUniqueSlug($request->en_title),
                'meta_title'       => $request->en_meta_title,
                'meta_description' => $request->en_meta_description,
                'meta_keywords'    => $request->en_meta_keywords,
            ]);

            $product->translations()->updateOrCreate(['locale' => 'ru'], [
                'title'            => $request->ru_title,
                'short_title'      => $request->ru_short_title,
                'description'      => $request->ru_description,
                'img_alt'          => $request->ru_img_alt,
                'img_title'        => $request->ru_img_title,
                'slug'             => $this->generateUniqueSlug($request->ru_title),
                'meta_title'       => $request->ru_meta_title,
                'meta_description' => $request->ru_meta_description,
                'meta_keywords'    => $request->ru_meta_keywords,
            ]);

            if ($request->hasFile('slider_images')) {
                foreach ($request->file('slider_images') as $file) {
                    $filename = $this->imageUploadService->upload($file, true);
                    $product->sliders()->create([
                        'image' => $filename,
                    ]);
                }
            }

            $data = $request->all();

            if (isset($data['filter_id'])) {


                
                DB::table('product_filter_options')->where('product_id', $product->id)->delete();

                foreach ($data['filter_id'] as $filterId) {
                    $selectedOptions = $data['selected_options'][$filterId] ?? [];

                    foreach ($selectedOptions as $optionId) {
                        $isDefault = isset($data['default_option'][$filterId]) && $data['default_option'][$filterId] == $optionId;
                        $isStock   = isset($data['is_stock'][$filterId][$optionId]);

                        $product->filters()->attach($filterId, [
                            'option_id'  => $optionId,
                            'is_default' => $isDefault,
                            'is_stock'   => $isStock,
                        ]);
                    }
                }

                
                activity()
                    ->inLog('Product') 
                    ->performedOn($product)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'filters'          => $data['filter_id'],
                        'selected_options' => $data['selected_options'] ?? []
                    ])
                    ->event('updated')
                    ->log('Product filters updated');




            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();

            return $exception->getMessage();
        }

        return redirect()->back()->with('message', 'Product updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {

        $product_translation = ProductTranslation::query()->where('product_id', $product->id)->first();
        $product_translation->delete();
        $product->delete();

        return redirect()->route('products.index')->with('message', 'Product deleted successfully');

    }

    public function uploadExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        Excel::import(new ProductsImport(), $request->file('file'));

        return redirect()->route('products.index')->with('success', 'Products imported successfully');
    }

    public function import(Request $request)
    {

        $request->validate([
                'xml_file' => 'required',
            ]);



        $file = $request->file('xml_file');
        $filePath = $file->storeAs('imports', uniqid() . '.xml'); // storage/app/imports/*.xml

        $fullPath = storage_path('app/' . $filePath);


        $xmlContent = file_get_contents($fullPath);
        $xml        = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json       = json_decode(json_encode($xml), true);
        $products   = $json['Product'] ?? [];

        foreach ($products as $product) {
            ProcessSingleProduct::dispatch(
                $product,
                $request->category_id,
                $request->sub_category_id,
                $request->third_category_id,
                $request->brand_id,
                auth()->id()
            );
        }


        // ProcessProductImport::dispatch(
        //     $filePath,
        //     $request->category_id,
        //     $request->sub_category_id,
        //     $request->third_category_id,
        //     $request->brand_id,
        //     auth()->id()
        // );

        return redirect()->back()->with('success', 'Yükləmə başladı, məhsullar fon rejimində əlavə olunacaq.');
    }

    public function zaraImport(Request $request)
    {

        $request->validate([
            'xml_file' => 'required',
        ]);

        $file = $request->file('xml_file');
        $xmlName = uniqid() . '.xml';
        $filePath = $file->storeAs('public/imports', $xmlName); 
        $filePath = '/imports/'.$xmlName;


        // $fullPath = public_path('storage/'.$filePath);

        // if (  file_exists($fullPath)) {


        //      $calculatorService = new PriceCalculatorService();
        // $xmlContent        = file_get_contents($fullPath);
       
        // $xml               = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);
        // $json              = json_decode(json_encode($xml), true);
        // $products          = $json['Urun'] ?? [];


        // $images = $products[0]['Resimler'] ?? [];
    

        //     if (is_array($images)) {
        //         $imageList = array_values(array_values($images));
        //         $imageList = collect($images)->flatten()->toArray();
        //         $imageList = array_filter($imageList, fn ($url) => is_string($url) && ! str_contains($url, 'transparent-background'));

        //         $imageList = array_filter($imageList, fn ($url) => is_string($url) && ! str_contains($url, 'transparent-background'));

               
        //     }

            
        //    dd(($products[0]));

        // }

        ZaraXmlImport::dispatch(
            $filePath,
            $request->category_id,
            $request->sub_category_id,
            $request->third_category_id,
            $request->brand_id,
            auth()->id()
        );

        return redirect()->back()->with('success', 'Yükləmə başladı, məhsullar fon rejimində əlavə olunacaq.');
    }

    public function increasePrices(Request $request)
    {
        $request->validate([
            'brand_id' => 'nullable|integer|exists:brands,id',
            'percent' => 'required|numeric|min:0',
        ]);

        $brandId = $request->input('brand_id');
        $percent = $request->input('percent');
        $multiplier = 1 + ($percent / 100);

        $query = DB::table('products');
        $brand = Brand::find($brandId);

        if ($brandId) {
            $query->where('brand_id', $brandId);
        }

       $query->update([
            'price' => DB::raw("ROUND(CASE WHEN discounted_price > 0 and discount > 0 THEN discounted_price * $multiplier ELSE price * $multiplier END)"),
            'discount' => null,
        ]);

        activity('Products')
        ->causedBy(auth()->user())
        ->performedOn($brand)
        ->withProperties([
            'brand_id' => $brandId,
            'brand' => $brand->title,
            'percent' => $percent.'%'
        ])
        ->event('price increase')
        ->log("Price increased by $percent% for brand \"$brand->title\"");


        return back()->with('success', 'Qiymətlər uğurla artırıldı.');
    }

    public function applyDiscount(Request $request)
    {
//        dd($request->all());
        $request->validate([
            'brand_id' => 'nullable|integer|exists:brands,id',
            'percent' => 'required|numeric|min:0|max:100',
        ]);

        $brandId = $request->input('brand_id');
        $brand = Brand::find($brandId);


        $percent = $request->input('percent');
        $multiplier = 1 - ($percent / 100);

        $query = DB::table('products');

        if ($brandId) {
            $query->where('brand_id', $brandId);
        }

        $query->update([
            'discounted_price' => DB::raw("ROUND(CASE WHEN discounted_price > 0 and discount > 0 THEN discounted_price * $multiplier ELSE price * $multiplier END)"),
            'discount' => $request->percent,
        ]);


        activity('Products')
        ->causedBy(auth()->user())
        ->performedOn($brand)
        ->withProperties([
            'brand_id' => $brandId,
            'brand' => $brand->title,
            'percent' => $percent.'%'
        ])
        ->event('price discount')
        ->log("Price decreased by $percent% for brand \"$brand->title\"");


        return back()->with('success', 'Endirim uğurla tətbiq edildi.');
    }


    public function deleteImage($id){
        $product = Product::findOrFail($id);
        

        activity('Products')
                ->causedBy(auth()->user()) 
                ->performedOn($product)     
                ->withProperties([
                    'image' => $product->image,
                ])
                ->event('deleted') 
            ->log("Şəkil uğurla silindi! product_id -> {$id}");

        $product->image=null;
        $product->save();

        return back()->with('message', 'Şəkil uğurla silindi!');
    }

    public function deleteProductBodyImage($id){
        $product = Product::findOrFail($id);
      
        activity('Products')
                ->causedBy(auth()->user()) 
                ->performedOn($product)     
                ->withProperties([
                    'size_image' => $product->size_image,
                ])
                ->event('deleted') 
            ->log("Şəkil uğurla silindi! product_id -> {$id}");

        $product->size_image=null;
        $product->save();

        return back()->with('message', 'Şəkil uğurla silindi!');
    }
}
