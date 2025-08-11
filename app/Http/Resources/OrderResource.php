<?php

namespace App\Http\Resources;

use App\Models\Status;
use App\Models\TopDeliveryStatus;
use App\Services\TopDeliveryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = new Status();
        $isCancel = $status->cancelFront();

        $checkHasStatus = false;
        
        $cancelableArray = $this->order_items->filter(function ($item) use(&$checkHasStatus)  {
            $status = $item?->lastStatus?->status;
            
            if($status){
                $checkHasStatus = true;
            }
            return $status?->cancelable;
        });

        

       
        $russianCargoFrontId = (new Status())->russianCargoFront()->id;

       

       

        $isCancelStatus= $this->order_items()->first()?->lastStatus?->status?->id==$isCancel->id;

        
       

      

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            // 'status' => $this->status,
            'is_deliver' => $this->is_deliver,
            'shop' => $this->shop,
            'payment_type' => $this->payment_type,
            'total_price' => $this->total_price,
            'discount' => $this->discount,
            'delivered_price' => $this->delivered_price,
            'final_price' => $this->final_price,
            'address' => $this->address,
            'additional_info' => $this->additional_info,
            'order_date' => $this->created_at->toDateTimeString(),
            'cancelTitle'=>$isCancelStatus ? $this->order_items()->first()?->lastStatus?->status?->title : '', //legv olunub text 
            'isCancel'=>$isCancelStatus, //sifaris legv olunubmu,
            'cancelable'=>(bool)count($cancelableArray) > 0 or !$checkHasStatus , //sifaris legv edile biler statusu,
            // 'isReturn'=>
            

            'addressChangeAble'=>(bool)count($cancelableArray) > 0 or !$checkHasStatus , //address deyisdirile biler statusu
           

//            'updated_at' => $this->updated_at->toDateTimeString(),
            // 'order_items' => OrderItemResource::collection($this->order_items->sortByDesc('id')),

            'order_items' => $this->order_items->
            when(request()->filled('status'), function($items){
                    return $items->filter(function($item){
                            return optional($item->lastStatus)->status_id == request()->status;
                    });
            })->
            sortByDesc('id')->map(function ($item) use($russianCargoFrontId) {

                  $checkStatus = null;
                  if($item->lastStatus?->status_id == $russianCargoFrontId){
                      $checkStatus = $this->checkOrderItemTopDelivery($item, $russianCargoFrontId);
                   }
                  
                
                    $product= $item->product;
                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        
                        // 'status' => $item->lastStatus?->status?->title,
                        // 'status_id' => $item->lastStatus?->status?->id,
                        // 'order_item_status' => $item->order_item_status,
                        'product' => [
                            'id' => $product?->id,
                            'title' => $product?->title,
                            //  'slug' =>
                            //         [
                            //             'en' => $product?->translate('en')->slug,
                            //             'ru' => $product?->translate('ru')->slug,
                            //         ],
                             'image' =>  $product?->image,

                        ],
                        'options' => $item->options->map(function ($option) {
                            return [
                                'filter' => $option->filter?->title,
                                'option' => $option->option?->title,
                            ];
                        }),
                        'total_price' => $item->quantity * $item->price,


                        'status'=>$item->lastStatus?->status_id == $russianCargoFrontId ? 
                        
                        TopDeliveryStatus::whereJsonContains('status_id', (string)optional($this->checkOrderItemTopDelivery($item, $russianCargoFrontId))['id'])->first()?->title
                        
                        : $item->lastStatus?->status?->title,



                        'statuses' => $item->statuses->sortBy('id')->map(function ($status) {
                            return [
                                'id' => $status->id,
                                'status' => $status->status?->title_ru,
                                'created_at' => \Carbon\Carbon::parse($status->created_at)->format('d.m.Y H:i:s'),
                            ];
                        }),

                    'statusDelivery' => $item->lastStatus?->status_id == $russianCargoFrontId
                        ? $this->checkOrderItemTopDelivery($item, $russianCargoFrontId)
                        : null,
                    "returnable" => $checkStatus and array_key_exists('id', $checkStatus) and $checkStatus['id'] == 22 ,
                    // "returnable" => true ,
                    
                    
                    ];
                })->values(),

        ];
    }


     public function checkShipmentTopDeliveryStatus($orderItem, $russianCargoFrontId){

        try{

            $deliveryService = new TopDeliveryService;
            $lastStatus = $orderItem->lastStatus;

    // davam et

            $shipment_id = $orderItem?->packages()?->first()?->boxes()?->first()?->shipment_id;



        if ($lastStatus && $lastStatus->status_id == $russianCargoFrontId) {
           

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

            $deliveryService = new TopDeliveryService;
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
           
                    // $statusModel = Status::where('topdelivery_status_id', $status->ordersInfo?->orderInfo?->status?->id)->first();

                    $statusModel = TopDeliveryStatus::whereJsonContains('status_id', (string)$status->ordersInfo?->orderInfo?->status?->id)
                    ->first();

                    return [
                        'id'=>$status->ordersInfo?->orderInfo?->status?->id,
                        'status'=>$statusModel ? $statusModel->title : $status->ordersInfo?->orderInfo?->status?->id,
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
            // return null;
            return response()->json([
                'message'=>$e->getMessage()
            ]);
        }
    }
}
