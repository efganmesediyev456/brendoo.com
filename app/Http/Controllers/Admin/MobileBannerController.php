<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\MobileBanner;
use App\Models\SubCategory;
use App\Models\ThirdCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MobileBannerController extends Controller
{
    public function __construct() {
        $this->middleware('permission:list-mobile-banners|create-mobile-banners|edit-mobile-banners', ['only' => ['index','show']]);
        $this->middleware('permission:create-mobile-banners', ['only' => ['create','store']]);
        $this->middleware('permission:edit-mobile-banners', ['only' => ['edit']]);
    }
    public function index()
    {

        $banners = MobileBanner::paginate(10);
        return view('admin.mobile-banners.index', compact('banners'));

    }

    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {
        $categories = Category::all();
        $subCategories = SubCategory::all();
        $thirdCategories = ThirdCategory::all();
        $brands = Brand::all();
        return view('admin.mobile-banners.create',
            compact('categories','subCategories','thirdCategories','brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'en_title'=>'required',
            'ru_title'=>'required',
            'en_description'=>'nullable',
            'ru_description'=>'nullable',
            'image'=>'required',
        ]);
        $logoName = '';
        $filename ='';

        if($request->hasFile('image')){
            $file = $request->file('image');
            $filename = Str::uuid() . "." . $file->extension();
            $file->storeAs('public',$filename);
        }
        

        if($request->hasFile('logo')){
            $file = $request->file('logo');
            $logoName = Str::uuid() . "." . $file->extension();
            $file->storeAs('public/',$logoName);
        }



      

        $filterConditions = collect($request->only([
            'category_id',
            'sub_category_id',
            'third_category_id',
            'min_price',
            'max_price',
            'brand_id',
        ]))
            ->filter()
            ->toArray();

        if ($request->has('is_discount') && $request->boolean('is_discount')) {
            $filterConditions['is_discount'] = 1;
        }

        if ($request->has('is_season') && $request->boolean('is_season')) {
            $filterConditions['is_season'] = 1;
        }

        if ($request->has('is_popular') && $request->boolean('is_popular')) {
            $filterConditions['is_popular'] = 1;
        }


        MobileBanner::create([
            'image'=>  $filename,
            'logo'=>  $logoName,
            'filter_conditions' => $filterConditions,
            'en'=>[
                'title'=>$request->en_title,
                'description'=>$request->en_description,
            ],
            'ru'=>[
                'title'=>$request->ru_title,
                'description'=>$request->ru_description,
            ]
        ]);

        return redirect()->route('mobile-banners.index')->with('message','Mobile Banner added successfully');

    }

    /**
     * Display the specified resource.
     */
    public function show(MobileBanner $banner)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MobileBanner $mobile_banner)
    {

        $categories = Category::all();
        $subCategories = SubCategory::all();
        $thirdCategories = ThirdCategory::all();
        $brands = Brand::all();
        $banner = $mobile_banner;
        return view('admin.mobile-banners.edit', compact('banner','categories','subCategories','thirdCategories','brands'));

    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, MobileBanner $mobile_banner)
    {
        $request->validate([
            'en_title'=>'required',
            'ru_title'=>'required',
            'en_description'=>'nullable',
            'ru_description'=>'nullable',
        ]);

        if($request->hasFile('image')){

            $file = $request->file('image');
            $filename = Str::uuid() . "." . $file->extension();
            $file->storeAs('public/',$filename);
            $mobile_banner->image = $filename;
        }
     
        if($request->hasFile('logo')){
            $file = $request->file('logo');
            $filename = Str::uuid() . "." . $file->extension();
            $file->storeAs('public/',$filename);
            $mobile_banner->logo = $filename;
        }
        $filterConditions = collect($request->only([
            'category_id',
            'sub_category_id',
            'third_category_id',
            'min_price',
            'max_price',
            'brand_id',
        ]))
            ->filter()
            ->toArray();

        if ($request->has('is_discount') && $request->boolean('is_discount')) {
            $filterConditions['is_discount'] = 1;
        }

        if ($request->has('is_season') && $request->boolean('is_season')) {
            $filterConditions['is_season'] = 1;
        }

        if ($request->has('is_popular') && $request->boolean('is_popular')) {
            $filterConditions['is_popular'] = 1;
        }



        $mobile_banner->update( [
            'filter_conditions' => $filterConditions, // Filtr şərtlərini yenilə
            'is_active'=> $request->is_active,
            'en'=>[
                'title'=>$request->en_title,
                'description'=>$request->en_description,
            ],
            'ru'=>[
                'title'=>$request->ru_title,
                'description'=>$request->ru_description,
            ]

        ]);

        return redirect()->back()->with('message','Mobile Banner updated successfully');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $banner)
    {
        $banner= MobileBanner::find($banner);
        $banner->delete();
        return redirect()->route('mobile-banners.index')->with('message', 'Mobile Banner deleted successfully');
    }
}
