<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SpecialResource;
use App\Http\Resources\StatusResource;
use App\Models\OrderItem;
use App\Models\Special;
use App\Models\Status;
use App\Models\TopDeliveryStatus;
use App\Services\TopDeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatusListController extends Controller
{


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


                    $statusModal =  TopDeliveryStatus::whereJsonContains('status_id', (string)$status->ordersInfo?->orderInfo?->status?->id)->first();
                    
                    
                    return [
                        'id'=>$status->ordersInfo?->orderInfo?->status?->id,
                        'status'=>$statusModal ? $statusModal->title : $status->ordersInfo?->orderInfo?->status?->id,
                        'created_at'=>is_array($status->ordersInfo?->orderInfo?->events) && $status->ordersInfo?->orderInfo?->events[0]?->date ? \Carbon\Carbon::parse($status->ordersInfo?->orderInfo?->events[0]?->date)->format('d.m.Y H:i:s') : null
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

    public function index(Request $request)
    {
        // try {
            $statuses = Status::where('type', 1)->get();
            // $statuses =collect();

            
            $maxId = $statuses->max("id");

            $russianCargoFront = (new Status)->russianCargoFront();

            // dd(OrderItem::whereHas("lastStatus", function($q) use($russianCargoFront){
            //     $q->where("status_id", $russianCargoFront->id);
            // })->pluck('id'));
           
            $orders = OrderItem::whereHas("lastStatus", function($q) use($russianCargoFront){
                $q->where("status_id", $russianCargoFront->id);
            })->get();


           
            if ($orders->isNotEmpty()) {
                foreach($orders as $item){
                   
                    $status = $this->checkOrderItemTopDelivery($item, $russianCargoFront->id);
                    
                    // dump($status);
                    if (!is_array($status) || !isset($status["id"], $status["status"])) {
                        return;
                    }

                    // dump($item->id, $russianCargoFront->id);

                    $topdeliveryId = $status["id"];
                    // $statusText = Status::where('topdelivery_status_id', $topdeliveryId)?->first();

                    $statuses->add(new Status([
                        "id" => ++$maxId,
                        "title_ru" => $status['status'],
                        "title_en" => $status['status'],
                        "type" => 1,
                        "topdelivery" => true,
                        "topdelivery_id" => $topdeliveryId
                    ]));
                };
            }



            $statuses = $statuses->unique('title');

            $data = StatusResource::collection($statuses)->resolve();




            
            return response()->json($data);
        // } catch (\Throwable $e) {
        //     return response()->json([
        //         'message' => 'Failed to get statuses.',
        //         'error' => $e->getMessage()
        //     ], 500);
        // }
    }

}
