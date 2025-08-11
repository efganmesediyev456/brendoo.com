<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderCancellationReason;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;

class OrderCancellationReasonsController extends Controller{


    public function __construct(protected ImageUploadService $imageUploadService)
    {
        $this->middleware('permission:list-order_cancellation_reasons|create-order_cancellation_reasons|edit-order_cancellation_reasons|delete-order_cancellation_reasons', ['only' => ['index','show']]);
        $this->middleware('permission:create-order_cancellation_reasons', ['only' => ['create','store']]);
        $this->middleware('permission:edit-order_cancellation_reasons', ['only' => ['edit']]);
        $this->middleware('permission:delete-order_cancellation_reasons', ['only' => ['destroy']]);
    }
    public function index(){
        $cancels = OrderCancellationReason::paginate(10);
        return view('admin.order_cancellation_reasons.index', compact('cancels'));
    }

    public function create(){
        return view('admin.order_cancellation_reasons.create');
    }

    public function store(Request $request){
        $this->validate($request, [
            'ru_title'=>'required',
            'en_title'=>'required'
        ]);

        $order = new OrderCancellationReason([
            'en'=>[
                'title'=>$request->en_title
            ],
            'ru'=>[
                'title'=>$request->ru_title
            ]
        ]);
        $order->save();
        return redirect()->route('order_cancellation_reasons.index')->withSuccess('Successfully added');
    }


    public function edit(Request $request, $id){
        $reason = OrderCancellationReason::findOrFail($id);
        return view('admin.order_cancellation_reasons.edit', compact('reason'));
    }

    public function update(Request $request, $id){
        $this->validate($request,[
            'ru_title'=>'required',
            'en_title'=>'required'
        ]);
        $order = OrderCancellationReason::findOrFail($id);
        $order->update([
             'en'=>[
                'title'=>$request->en_title
            ],
            'ru'=>[
                'title'=>$request->ru_title
            ]
        ]);

        return redirect()->route('order_cancellation_reasons.index')->withSuccess('Successfully updated');
    }


    public function destroy(Request $request, $id){
       $reason = OrderCancellationReason::findOrFail($id);
       $reason->delete();
       return redirect()->route('order_cancellation_reasons.index')->withSuccess('Successfully deleted');
    }
}