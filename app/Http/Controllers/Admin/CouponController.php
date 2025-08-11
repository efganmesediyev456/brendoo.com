<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\DemandPaymentBalanceOrder;
use App\Models\Influencer;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{

    public function __construct(protected ImageUploadService $imageUploadService)
    {
        $this->middleware('permission:list-coupons|create-coupons|edit-coupons|delete-coupons', ['only' => ['index','show']]);
        $this->middleware('permission:create-coupons', ['only' => ['create','store']]);
        $this->middleware('permission:edit-coupons', ['only' => ['edit']]);
        $this->middleware('permission:delete-coupons', ['only' => ['destroy']]);
    }

    public function index()
    {
        $coupons = null;
        if(request()->has('influencer') && request()->filled('influencer')){
            $influencer = Influencer::find(request()->influencer);
            $coupons = $influencer->coupons()->orderBy('id','desc')->paginate(10);
        }else{
            $coupons = Coupon::query()->orderBy('id','desc')->paginate(20);
        }
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        $influencers = Influencer::get();
        return view('admin.coupons.create', compact("influencers"));
    }

    // Store a new coupon in the database (store)
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:coupons,code',
            'discount' => 'required|numeric',
            'type' => 'required|in:percentage,amount',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'earn_price' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Creating a new coupon
        
        $coupon=Coupon::create([
            'code' => $request->input('code'),
            'discount' => $request->input('discount'),
            'type' => $request->input('type'),
            'valid_from' => $request->input('valid_from'),
            'valid_until' => $request->input('valid_until'),
            'is_active' => $request->input('is_active', true), 
            'coupon_type' => (int)$request->input('coupon_type', false), 
            'earn_price'=>$request->earn_price,
            "influencer_id"=>$request->influencer_id
        ]);


         $coupon->translations()->createMany([
                [
                    'locale'           => 'en',
                    'title'            => $request->en_title,
                ],
                [
                    'locale'           => 'ru',
                    'title'            => $request->ru_title,
                ],
            ]);

        return redirect()->route('coupons.index',['influencer'=>$coupon->influencer_id])->with('success', 'Coupon created successfully.');
    }

    // Show the form to edit an existing coupon (edit)
    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        $influencers = Influencer::get();

        return view('admin.coupons.edit', compact('coupon','influencers'));
    }

    // Update an existing coupon (update)
    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        // Validation
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:coupons,code,' . $coupon->id,
            'discount' => 'required|numeric',
            'type' => 'required|in:percentage,amount',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'coupon_type' => 'required',
            'earn_price'=>'required',
            
        ]);


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }


        // Updating the coupon
        $coupon->update([
            'code' => $request->input('code'),
            'discount' => $request->input('discount'),
            'type' => $request->input('type'),
            'valid_from' => $request->input('valid_from'),
            'valid_until' => $request->input('valid_until'),
            'is_active' => $request->input('is_active', true),
            'coupon_type' => $request->input('coupon_type', false),
            'earn_price'=>$request->earn_price,
            "en"=>[
                "title"=>$request->input("en_title")
            ],
            "ru"=>[
                "title"=>$request->input("ru_title")
            ],
            "influencer_id"=>$request->influencer_id
        ]);

        return redirect()->back()->with('success', 'Coupon updated successfully.');
    }

    // Delete an existing coupon (delete)
    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);

        // Deleting the coupon
        $coupon->delete();

        return redirect()->back()->with('success', 'Coupon deleted successfully.');
    }


    public function products($coupon){
        $uses= DemandPaymentBalanceOrder::where('coupon_id', $coupon)->
        whereHas('demandPaymentBalance', function($demandPaymentBalance){
            return $demandPaymentBalance->where('influencer_id',request()->influencer);
        })->
        paginate(10);
        return view('influencers.balances.coupons.index', compact('uses'));
    }
}
