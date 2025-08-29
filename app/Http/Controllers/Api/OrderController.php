<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Enums\AdminOrderStatus;
use App\Http\Enums\OrderStatus;
use App\Http\Resources\OrderResource;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Status;
use App\Services\TopDeliveryService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Log;

class OrderController extends Controller
{
    private function calculateDiscount(Coupon $coupon, float $totalPrice): float
    {
        if ('percentage' === $coupon->type) {
            return $totalPrice * ($coupon->discount / 100);
        }

        return round($coupon->discount, 2);
    }

    public function storeOrder(Request $request): JsonResponse
    {
        DB::beginTransaction();


        try {
            $customer    = Customer::query()->findOrFail(auth()->user()->id);
            $basketItems = $customer->basketItems()->with('options')->get();

            if ($basketItems->isEmpty()) {
                return response()->json(['message' => 'Basket is empty.'], 400);
            }

            $validator = Validator::make($request->all(), [
                'address' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()->all(),
                ], 422);
            }

            $couponData = null;

            $discountAmount = $request->discount;

            $finalPrice = $request->final_price;

            $order = $customer->orders()->create([
                'status'       => OrderStatus::Pending,
                'order_number' => mb_strtoupper(uniqid('ORDER_')),
                'is_deliver'   => $request->input('is_deliver', false),
                'shop'         => $request->input('shop'),
                'payment_type' => $request->input('payment_type', 'cash'),
                'total_price'  => $request->total_price,
                'discount'     => $discountAmount,
                //                'delivered_price' => $request->delivered_price,
                'final_price'     => $finalPrice,
                'address'         => $request->input('address'),
                'additional_info' => $request->input('additional_info'),
                'region'        => $request->input('region'),
                'city'            => $request->input('city'),
                'regionId'        => $request->input('regionId'),
                'cityId'            => $request->input('cityId'),
                'coupon_id' => $request->coupon_id
            ]);

            foreach ($basketItems as $basketItem) {



                $orderItem = $order->order_items()->create([
                    'status'       => OrderStatus::Pending,
                    'admin_status' => AdminOrderStatus::Pending,
                    'product_id'   => $basketItem->product_id,
                    'quantity'     => $basketItem->quantity,
                    'price'        => $basketItem->price,
                    'customer_id'  => auth()->user()->id,
                    'collection_id' => $basketItem->collection_id
                ]);




                foreach ($basketItem->options as $option) {
                    $orderItem->options()->create([
                        'filter_id' => $option->filter_id,
                        'option_id' => $option->option_id,
                    ]);
                }


            }



            if ($couponData) {
                $order->coupons()->attach($couponData['coupon']->id);
            }



            // $customer->basketItems()->delete();

            DB::commit();

            return response()->json(['order' => new OrderResource($order)], 201);
        } catch (Exception $exception) {
            DB::rollBack();

            Log::error('Order creation failed', ['error' => $exception->getMessage()]);

            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }


    public function checkShipmentTopDeliveryStatus($orderItem, $russianCargoFrontId){

        try{

            $deliveryService = new TopDeliveryService;
            $lastStatus = $orderItem->lastStatus;

    // davam et

            $shipment_id = $orderItem?->packages()?->first()?->boxes()?->first()?->shipment_id;


         

        if ($lastStatus && $lastStatus->status_id == $russianCargoFrontId && $shipment_id) {
           

            $package = $orderItem->packages->first();
            $data = [
                'orderId'=>$package->top_delivery_order_id,
                'barcode'=>$package->barcode,
                'webshopNumber'=>$package->webshop_number
            ];
            $status = $deliveryService->getShipmentsInfo([$shipment_id]);


            if($status->requestResult){
                if( $status->requestResult->status==0){
                    return [
                        'id'=>$status->shipmentsInfo->shipmentInfo?->status?->id,
                        'status'=>$status->shipmentsInfo->shipmentInfo?->status?->name,
                        'created_at'=>$status?->shipmentsInfo?->shipmentInfo?->dateCreate,
                    ];
                }else{
                  throw new Exception('Topdelivery status elde eden zaman xeta');
                }
            }else{
                throw new Exception('Topdeliveryden status elde edile bilmedi');
            }
        }

        }catch(\Exception $e){
            // return null;
            return response()->json([
                'message'=>$e->getMessage()
            ]);
        }
    }


    public function checkOrderItemTopDelivery($orderItem, $russianCargoFrontId){

        try{



          
         
            $deliveryService = new TopDeliveryService();
            $lastStatus = $orderItem->lastStatus;
    // davam et


        if ($lastStatus && $lastStatus->status_id == $russianCargoFrontId) {
           
            $package = $orderItem->packages->first();
            $data = [
                'orderId'=>$package->top_delivery_order_id,
                'barcode'=>$package->barcode,
                'webshopNumber'=>$package->webshop_number
            ];
            $status = $deliveryService->getOrdersInfo($data);

            
           

            if($status->requestResult){
                if( $status->requestResult->status==0){
                   
                    return [
                        'id'=>$status->ordersInfo?->orderInfo?->status?->id,
                        'status'=>$status->ordersInfo?->orderInfo?->status?->name,
                        'created_at'=>is_array($status->ordersInfo?->orderInfo?->events) ? \Carbon\Carbon::parse($status->ordersInfo?->orderInfo?->events[0]?->date)->format('d.m.Y H:i:s') : \Carbon\Carbon::parse($status->ordersInfo?->orderInfo?->events?->date)->format('d.m.Y H:i:s')
                    ];

                }else{
                  throw new Exception('Topdelivery status elde eden zaman xeta');
                }
            }else{
                throw new Exception('Topdeliveryden status elde edile bilmedi');
            }
        }

        }catch(\Exception $e){
            return null;
            return response()->json([
                'message'=>$e->getMessage()
            ]);
        }
    }
    public function applyCoupon(Request $request): JsonResponse
    {
        DB::beginTransaction();
        $couponCode = $request->coupon_code;
        $totalPrice = $request->total_price;
        $coupon     = Coupon::query()->where('code', $couponCode)->first();
        $customer   = Customer::query()->findOrFail(auth()->user()->id);


        // dd($request->all());

        if ( ! $coupon || ! $coupon->isValid()) {
            return response()->json(['error' => 'Invalid or expired coupon code.'], 422);
        }

        if ($customer->coupons->contains($coupon->id)) {
            return response()->json(['error' => 'You have already used this coupon.'], 422);
        }

        $influencer = $coupon->influencer;

        $customer->coupons()->attach($coupon->id);

        $discountAmount = round($totalPrice - $this->calculateDiscount($coupon, $totalPrice), 2);

        // $influencer->balances()->create([
        //     "amount"=>$coupon->earn_price,
        //     "type"=>"IN",
        //     "balance_type"=>"coupon",
        //     "customer_id"=>auth()->guard('customers')->user()->id,
        //     "coupon_id" => $coupon->id
        // ]);
        DB::commit();

        return response()->json(['coupon' => $coupon, 'discounted_total_price' => $discountAmount]);
    }

    public function getOrders(Request $request)
    {
        $status = $request->status;

        // $customer = Customer::query()
        //     ->with(['orders' => function ($query) use ($status): void {
        //         $query->with(['order_items' => function ($query) use ($status): void {
        //             if ($status) {
        //                 $query->where('status', $status);
        //             }
                    
        //         }])->withCount('order_items');
        //     },
            
        //     ])
        //     ->findOrFail(auth()->user()->id);
        // $orders = $customer->orders;


        $customer = auth('customers')->user();
       
        $orders = $customer->orders()->with([
            'order_items.lastStatus.status',
            'order_items.options.filter',
            'order_items.options.option',
            'order_items.statuses.status',
            'order_items.product.translations', 
            'order_items.packages', 
            'order_items'
        ]);

        if($request->filled('status') and $status != ''){
            $orders = $orders->whereHas('order_items.lastStatus', function($q) use($status){
                return $q->where('status_id', $status);
            });
        }
        
        $orders = $orders->get()->sortByDesc('id');
        $russianCargoFront = (new Status)->russianCargoFront();

        if($request->filled('topdelivery_status') and $request->topdelivery_status != ''){
           

            // dd( $request->topdelivery_status);
            $orders = $orders->filter(function($order) use($russianCargoFront, $request){
                foreach($order->order_items as $orderItem){

                    $topdeliveryStatus = $orderItem->lastStatus?->status_id == $russianCargoFront->id
                        ? $this->checkOrderItemTopDelivery($orderItem, $russianCargoFront->id)
                        : null;


                    if ($topdeliveryStatus && $topdeliveryStatus['id'] == $request->topdelivery_status) {
                        return true;
                    }
                }
                return false;
            });
        }

        $orders = $orders->filter(function($order) {
            return $order->payments->contains('status', 'APPROVED');
        })->sortByDesc('id');

     
        return response()->json(OrderResource::collection($orders));
    }

    public function getOrderItem($id): JsonResponse
    {
        try {
            $order = Order::query()->with('order_items')->withCount('order_items')->findOrFail($id);
            return response()->json(new OrderResource($order));
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order not found.',
                'status'  => 404,
            ], 404);
        }
    }

    
  

    public function cancelOrder(Request $request, $order): JsonResponse
    {
        $request->validate([
            'cancel_id'   => 'nullable|exists:order_cancellation_reasons,id',
            'cancel_note' => 'nullable',
        ]);
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($order);

            $status = new Status();
            $cancelAdmin = $status->cancelAdmin();
            $cancelFront = $status->cancelFront();

            foreach ($order->order_items as $orderItem) {
                $orderItem->admin_status = $cancelAdmin->id;
                $orderItem->save();

                $orderItem->statuses()->create([
                    'status_id' => $cancelFront->id
                ]);
            }
            if($request->filled('cancel_id') || $request->filled('cancel_note')){
                $order->orderCancellation()->create([
                    'reason_id'=>$request->cancel_id,
                    'text'=>$request->cancel_note
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Order canceled successfully.']);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong while canceling the order.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function changeOrderAddress(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id'        => 'required|exists:orders,id',
            'address'         => 'required|string',
            'additional_info' => 'nullable|string',
        ]);

        $order = Order::findOrFail($validated['order_id']);
        $order->address = $validated['address'];
        if($request->has('regionId')){
            $order->regionId = $request->region_id;
        }
        if($request->has('cityId')){
            $order->cityId = $request->city_id;
        }
        if (isset($validated['additional_info'])) {
            $order->additional_info = $validated['additional_info'];
        }
        $order->save();
        return response()->json([
            'message' => 'Address changed successfully.',
            'order'   => $order->only(['id', 'address', 'additional_info']),
        ]);
    }


    public function returnOrder(Request $request, $orderItem){
        try{
            DB::beginTransaction();

            $orderItem = OrderItem::findOrFail($orderItem);

            $status = new Status();
            $frontReturn = $status->returnFront();
            $returnAdmin = $status->returnAdmin();
            
            
            $orderItem->admin_status = $returnAdmin->id;
            $orderItem->save();

            $orderItem->statuses()->create([
                'status_id' => $frontReturn->id
            ]);

            DB::commit();

            return response()->json(['message' => 'Order Item returned successfully.']);

        }catch(\Exception $e){
            return response()->json([
                'message'=> $e->getMessage(),
            ], 500);
        }
    }
}
