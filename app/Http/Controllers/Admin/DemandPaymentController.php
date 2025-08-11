<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DemandPaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\DeliveryPrice;
use App\Models\DemandPayment;
use Exception;
use Illuminate\Http\Request;
use DB;

class DemandPaymentController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:list-demand-payments|create-demand-payments|edit-demand-payments|delete-demand-payments', ['only' => ['index','show']]);
        $this->middleware('permission:create-demand-payments', ['only' => ['create','store']]);
        $this->middleware('permission:edit-demand-payments', ['only' => ['edit']]);
        $this->middleware('permission:delete-demand-payments', ['only' => ['destroy']]);
    }

    public function index()
    {

        $demandPayments = DemandPayment::query()->paginate(10);
        return view('admin.demand-payments.index', compact('demandPayments'));

    }

    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {

        return view('admin.demand-payments.create');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'title'=>'required',
        ]);

        DeliveryPrice::create([
            'title'=>$request->title,
        ]);

        return redirect()->route('demand-payments.index')->with('message','DeliveryPrice added successfully');

    }

    /**
     * Display the specified resource.
     */
    public function show(DeliveryPrice $delivery_price)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DeliveryPrice $delivery_price)
    {

        return view('admin.demand-payments.edit', compact('delivery_price'));

    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, DeliveryPrice $delivery_price)
    {

        $request->validate([
            'title'=>'required|numeric',
        ]);

        $delivery_price->update( [

            'title'=>$request->title,

        ]);

        return redirect()->back()->with('message','DeliveryPrice updated successfully');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeliveryPrice $delivery_price)
    {

        $delivery_price->delete();
        return redirect()->route('demand-payments.index')->with('message', 'DeliveryPrice deleted successfully');

    }


    public function statusChange(Request $request){
        $this->validate($request,[
            "status"=>"required",
            "dataId"=>"required"
        ]);
        try{

         DB::beginTransaction();


         $demandPayment = DemandPayment::where('id', $request->dataId)->first();
         $demandPayment->status = $request->status;
         $demandPayment->save();
         $influencer= $demandPayment->influencer;


         if($request->status == DemandPaymentStatusEnum::Paid->value){
            $influencer->balances()->create([
                    "amount"=>$demandPayment->amount,
                    "type"=>"OUT",
                    "balance_type"=> $demandPayment->type
            ]);
         }else{
              $influencer->balances()
                ->where('balance_type', $demandPayment->type)
                ->where('amount', $demandPayment->amount)
                ->where('type', 'OUT')
                ->latest()
                ->first()?->delete();
         }


         

         DB::commit();

         
         return $this->responseMessage('success','Successfully Status Changed',[
            'status'=>$demandPayment->refresh()->status->label()
         ],200, null);

        }catch(\Exception $e){
            DB::rollBack();
            return $this->responseMessage('error',$e->getMessage(), null,500, null);
        }

    }
}
