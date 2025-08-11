<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReturnProductResource;
use App\Http\Resources\ReturnReasonResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnProduct;
use App\Models\ReturnReason;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturnProductController extends Controller
{


    public function index(): JsonResponse
    {

        // $returns = ReturnProduct::query()
        //     ->where('customer_id', auth()->user()->id)
        //     ->with('orderItem.product')
        //     ->get();



        $statusFront= (new Status)->returnFront();
        $statusAdmin= (new Status)->returnAdmin();

        // $returns = Order::whereHas("order_items", function($q) use($statusFront){
        //     $q->whereHas("statuses", function($qq) use($statusFront){
        //         $qq->where("status_id", $statusFront->id);
        //     });
        // })->get();

        $returns = OrderItem::whereHas("lastStatus", function($qq) use($statusFront){
                $qq->where("status_id", $statusFront->id);
                $qq->orWhereHas("status", function($qqq){
                    return $qqq->where("is_return_status",1);
                });
        })->get();
        

        return response()->json(ReturnProductResource::collection($returns));
    }



    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'order_item_id' => 'required',
                "name"=>"required",
                "phone"=>"required",
                "email"=>"required",
                "address"=>"required",
                "return_reason_id"=>"required"
            ]);
            $statusFront= (new Status)->returnFront();
            $statusAdmin= (new Status)->returnAdmin();
            $orderItem = OrderItem::find($request->order_item_id);
            $orderItem->update([
                'admin_status' => $statusAdmin->id,
            ]);
            $orderItem->statuses()->create([
                'status_id'=>$statusFront->id
            ]);
            $orderItem->returnProduct()->create([
                "name"=>$request->name,
                "phone"=>$request->phone,
                "email"=>$request->email,
                "address"=>$request->address,
                "return_reason_id"=>$request->return_reason_id,
                "notes"=>$request->notes,
                "customer_id"=>$request->user()?->id
            ]);
            return response()->json([
                'message' => 'Geri qaytarma tələbi uğurla göndərildi!',
                'data' => $orderItem
            ], 201);
        }catch (\Exception $exception){
            return response()->json([
                "message"=>$exception->getMessage()
            ]);
        }
    }


    public function returnReason(Request $request){
        $returnReasons = ReturnReason::get();
        return response()->json(ReturnReasonResource::collection($returnReasons));
    }

}
