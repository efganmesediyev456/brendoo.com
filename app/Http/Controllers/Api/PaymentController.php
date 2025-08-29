<?php

namespace App\Http\Controllers\Api;

use App\Enums\DemandPaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Enums\AdminOrderStatus;
use App\Http\Enums\OrderStatus;
use App\Models\Collection;
use App\Models\Coupon;
use App\Models\DemandPaymentBalanceOrder;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Status;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function createPayment(Request $request)
    {

        $paymentData = $request->validate([
            'final_price' => 'required|min:0'
        ]);

        $response = $this->paymentService->createPayment($paymentData);

        Payment::create([
            'order_id' => null,
            'customer_id' => auth()->user()->id,
            'operation_id' => $response['Data']['operationId'],
            'amount' => $response['Data']['amount'],
            'status' => $response['Data']['status'],
            'payment_method' => null,
            'meta' => json_encode($response['Data']),
        ]);

        $link = $response['Data']['paymentLink'];
        $operationId = $response['Data']['operationId'];

        return response()->json([
            'payment_link' => $link,
            'operation_id' => $operationId,
        ]);

    }

    // public function checkOrderPayment(Request $request)
    // {
    //     $request->validate([
    //         'operation_id' => 'required',
    //         'order_id' => 'required'
    //     ]);

       


    //     $response = $this->paymentService->checkPaymentStatus($request->operation_id);
    //     $status = $response['Data']['Operation'][0]['status'];

    //     $payment = Payment::query()->where('operation_id', $request->operation_id)->first();
    //     $payment->update([
    //         // 'status' => $status,
    //         'status' => "APPROVED",
    //         'order_id' => $request->order_id
    //     ]);
       
    //     // if ($response['Data']['Operation'][0]['status'] == 'APPROVED'){
    //         $order = Order::query()->with('order_items')->where('id', $request->order_id)->first();
    //         $status = new Status();
    //         $paidStatusAdmin = $status->paidStatusAdmin();
    //         $paidStatusFront = $status->paidStatusFront();
    //         $collections = [];

    //         foreach ($order->order_items as $order_item){
    //             $order_item->update([
    //                     'admin_status' => $paidStatusAdmin->id,
    //             ]);
    //             $order_item->statuses()->create([
    //                     'status_id'=>$paidStatusFront->id
    //             ]);
                

    //             $collection = $order_item->collection;
    //             if ($collection) {
    //                 $collections[$collection->id]['collection'] = $collection;
    //                 $collections[$collection->id]['items'][] = $order_item;
    //             }
    //         }

            

    //         foreach ($collections as $collectionId=>$data) {
    //             $collection = $data['collection'];
    //             $items = $data['items'];

    //             $totalPrice = collect($items)->sum(function ($item) {
    //                 return $item->price*$item->quantity; //
    //             });
    //             $earnPercent = $collection->earn_price; 
    //             $earnedAmount = ($totalPrice * $earnPercent) / 100;
    //             $collection = Collection::find($collectionId);
            
    //             $influencer = $collection->influencer;



    //             if ($influencer && $earnedAmount > 0 && $collection->status) {
                
    //                 //balans artimi
    //                 $influencer->balances()->create([
    //                     'amount' => $earnedAmount,
    //                     'type' => 'IN',
    //                     'balance_type' => 'collection',
    //                     'customer_id' => array_pop($items)?->customer_id,
    //                     "collection_id" => $collectionId
    //                 ]);

    //                 //teleb olan odenis 
    //                 $existing = $influencer->demandPayments()
    //                     ->where('type', 'collection')
    //                     ->where('status', DemandPaymentStatusEnum::NotRequested)
    //                     ->first();

    //                 if ($existing) {
    //                     $existing->update([
    //                         'amount' => $existing->amount + $earnedAmount
    //                     ]);
    //                 } else {
    //                     $existing = $influencer->demandPayments()->create([
    //                         'type' => 'collection',
    //                         'status' => DemandPaymentStatusEnum::NotRequested,
    //                         'amount' => $earnedAmount,
    //                     ]);
    //                 }

    //                 foreach($data['items'] as $orderItem){
    //                         DemandPaymentBalanceOrder::create([
    //                             "demand_payment_balance_id" => $existing->id,
    //                             "order_item_id" => $orderItem->id,
    //                         ]);
    //                 }
                    

    //             }
    //         }

    //         $customer = $order?->customer;
    //         $customer?->basketItems()?->delete();
    //     // }
    //     return response()->json([
    //         'success' => true
    //     ]);
    // }




    public function checkOrderPayment(Request $request)
    {
        $request->validate([
            'operation_id' => 'required',
            'order_id' => 'required'
        ]);

        DB::beginTransaction();


        try {
            $response = $this->paymentService->checkPaymentStatus($request->operation_id);
            $status = $response['Data']['Operation'][0]['status'];


            $payment = Payment::query()->where('operation_id', $request->operation_id)->first();
            $payment->update([
                // 'status' => "APPROVED", 
                'status' => $status, 
                'order_id' => $request->order_id
            ]);

            if ($response['Data']['Operation'][0]['status'] == 'APPROVED'){


                $order = Order::query()->with('order_items')->where('id', $request->order_id)->first();
                $statusObj = new Status();
                $paidStatusAdmin = $statusObj->paidStatusAdmin();
                $paidStatusFront = $statusObj->paidStatusFront();

                $collections = [];

            
                foreach ($order->order_items as $order_item) {
                    $order_item->update([
                        'admin_status' => $paidStatusAdmin->id,
                    ]);

                    $order_item->statuses()->create([
                        'status_id' => $paidStatusFront->id
                    ]);

                    $collection = $order_item->collection;
                    if ($collection) {
                        $collections[$collection->id]['collection'] = $collection;
                        $collections[$collection->id]['items'][] = $order_item;
                    }
                }

                foreach ($collections as $collectionId => $data) {

                
                    $collection = $data['collection'];
                    $items = $data['items'];

                    $totalPrice = collect($items)->sum(function ($item) {
                        return $item->price * $item->quantity;
                    });

                    $earnPercent = $collection->earn_price;
                    $earnedAmount = round(($totalPrice * $earnPercent) / 100, 2);
                    $collection = Collection::find($collectionId);

                    $influencer = $collection->influencer;

                    if ($influencer && $earnedAmount > 0 && $collection->status) {
                        $influencer->balances()->create([
                            'amount' => $earnedAmount,
                            'type' => 'IN',
                            'balance_type' => 'collection',
                            'customer_id' => array_pop($items)?->customer_id,
                            'collection_id' => $collectionId
                        ]);

                        $existing = $influencer->demandPayments()
                            ->where('type', 'collection')
                            ->where('status', DemandPaymentStatusEnum::NotRequested)
                            ->first();

                        if ($existing) {
                            $existing->update([
                                'amount' => $existing->amount + $earnedAmount
                            ]);
                        } else {
                            $existing = $influencer->demandPayments()->create([
                                'type' => 'collection',
                                'status' => DemandPaymentStatusEnum::NotRequested,
                                'amount' => $earnedAmount,
                            ]);
                        }

                        

                        foreach ($data['items'] as $orderItem) {

                            $coupon = $order->coupon;
                            if($coupon){
                                $demandPaymentOrderPrice = round($orderItem->price  - $this->calculateDiscount($coupon, $orderItem->price),2);
                            }else{
                                $demandPaymentOrderPrice = round($orderItem->price, 2);
                            }
                            $pr = round($demandPaymentOrderPrice * $collection->earn_price / 100, 2, PHP_ROUND_HALF_UP);

                        
                            DemandPaymentBalanceOrder::create([
                                'demand_payment_balance_id' => $existing->id,
                                'order_item_id' => $orderItem->id,
                                'amount' => $pr,
                                'type' => 'collection',
                                'customer_id' => $order->customer_id,
                                'collection_id'=>$collectionId
                            ]);
                        }
                    }
                }

                if($order->coupon_id){
                    $coupon = $order->coupon;
                    $amount = round($order->final_price * $coupon->earn_price / 100, 2);
                    $influencer = $coupon->influencer;
                    if($influencer){
                        $influencer->balances()->create([
                                            "amount"=>$amount,
                                            "type"=>"IN",
                                            "balance_type"=>"coupon",
                                            "customer_id"=>auth()->guard('customers')->user()->id,
                                            "coupon_id" => $coupon->id
                                        ]);



                        $existing = $influencer->demandPayments()
                                ->where('type', 'coupon')
                                ->where('status', DemandPaymentStatusEnum::NotRequested)
                                ->first();

                        if ($existing) {
                            $existing->update([
                                'amount' => $existing->amount + $amount
                            ]);
                        } else {
                            $existing = $influencer->demandPayments()->create([
                                'type' => 'coupon',
                                'status' => DemandPaymentStatusEnum::NotRequested,
                                'amount' => $amount,
                            ]);
                        }

                        foreach ($order->order_items as $orderItem) {
                            $demandPaymentOrderPrice = round($orderItem->price  - $this->calculateDiscount($coupon, $orderItem->price),2);
                            
                            $pr = round($demandPaymentOrderPrice*$coupon->earn_price/100, 2);
                            DemandPaymentBalanceOrder::create([
                                'demand_payment_balance_id' => $existing->id,
                                'order_item_id' => $orderItem->id,
                                'amount' => $pr,
                                'type' => 'coupon',
                                'customer_id' => $order->customer_id,
                                'coupon_id'=>$coupon->id
                            ]);
                        }
                    }
                }
                $customer = $order?->customer;
                $customer?->basketItems()?->delete();

            }

            DB::commit();

            return response()->json([
                'success' => true
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            logger()->error('Order payment check failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Xəta baş verdi, əməliyyat geri alındı.'.$e->getMessage()
            ], 500);
        }
    }


    private function calculateDiscount(Coupon $coupon, float $totalPrice): float
    {
        if ('percentage' === $coupon->type) {
            return $totalPrice * ($coupon->discount / 100);
        }

        return round($coupon->discount, 2);
    }

}
