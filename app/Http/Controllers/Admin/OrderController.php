<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemStatus;
use App\Models\Status;
use App\Models\TopDeliveryStatus;
use App\Services\OrderStatusService;
use App\Services\PaymentService;
use App\Services\TopDeliveryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct(protected OrderStatusService $orderStatusService) {
        $this->middleware('permission:list-orders|create-orders|edit-orders|delete-orders', ['only' => ['index', 'show']]);
        $this->middleware('permission:create-orders', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-orders', ['only' => ['edit']]);
        $this->middleware('permission:delete-orders', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
       
        $status       = $request->filled('status') ? $request->status : null;
        $admin_status = $request->filled('admin_status') ? $request->admin_status : null;

        $query = Order::query()->whereHas('payments', function($qq){
            return $qq->where("status","APPROVED");
        })->orderByDesc('id');

        if($status){
            $query->whereHas('order_items', function($ordQuery) use($status){
                $ordQuery->whereHas('lastStatus', function($ordStatus) use($status){
                    $ordStatus->where('status_id', $status);
                });
            });
        }
        if($admin_status){
            $query->whereHas('order_items', function($ordQuery) use($admin_status){
                $ordQuery->where('admin_status', $admin_status);
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('customer_mail')) {
            $query->whereHas('customer', function ($q) use ($request): void {
                $q->where('email', 'like', '%' . $request->customer_mail . '%');
            });
        }

        if ($request->filled('name')) {
            $query->whereHas('customer', function ($q) use ($request): void {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
            $endDate   = \Carbon\Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $adminStatuses = Status::where('type', 0)->get();
        $frontStatuses = Status::where('type', 1)->get();
        

        $orders = $query->paginate(20);
        

        // dd($orders);

        return view('admin.orders.index', compact('orders','adminStatuses','frontStatuses'));
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

           

            

            $existingStatuses = TopDeliveryStatus::whereJsonContains('status_id', (string)$status->ordersInfo?->orderInfo?->status?->id)
                    ->first();

                 

            if($status->requestResult){
                if( $status->requestResult->status==0){
           
                    $statusModel = Status::where('topdelivery_status_id', $status->ordersInfo?->orderInfo?->status?->id)->first();

                 
                    return [
                        'id'=>$status->ordersInfo?->orderInfo?->status?->id,
                        'status'=>$existingStatuses ? $existingStatuses->title_ru : $status->ordersInfo?->orderInfo?->status?->id,
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


    public function show($id)
    {
        $order      = Order::query()->with('order_items')->findOrFail($id);
        $orderItems = $order->order_items()->get();
        

        $russianCargoFrontId = 9; 

        foreach ($orderItems as $item) {
            $statusInfo = $this->checkOrderItemTopDelivery($item, $russianCargoFrontId);

            if ($statusInfo instanceof \Illuminate\Http\JsonResponse) {
                $item->topdelivery_status = $statusInfo->getData(true);
            } else {
                $item->topdelivery_status = $statusInfo;
            }
        }



        return view('admin.orders.show', compact('orderItems', 'order'));
    }

    //    public function updateStatus(Request $request, $id)
    //    {
    //        $request->validate([
    //            'status' => ['required', Rule::in(array_column(OrderStatus::cases(), 'value'))],
    //        ]);
    //
    //        $order = Order::query()->findOrFail($id);
    //        $order->status = $request->status;
    //        $order->save();
    //
    //        return redirect()->back()->with('message', 'Sifariş statusu uğurla dəyişdirildi!');
    //    }

    public function cancelOrder(Request $request, $orderId)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $order = Order::findOrFail($orderId);

        if ($order->customer_id !== auth()->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($order->status === OrderStatus::Cancelled->value) {
            return response()->json(['message' => 'Order already canceled'], 400);
        }

        $order->update([
            'status'        => OrderStatus::Cancelled->value,
            'cancel_reason' => $request->reason,
        ]);

        return response()->json(['message' => 'Order canceled successfully']);
    }

    public function toggle_order_item_status($id)
    {
        $order_item = OrderItem::query()->findOrFail($id);

        $order_item->order_item_status = ! $order_item->order_item_status;
        $order_item->save();

        $order              = $order_item->order;
        $order->final_price = $order->order_items()
            ->where('order_item_status', true)
            ->sum('price');
        $order->save();

        return redirect()->back()->with('message', 'Status uğurla dəyişdirildi.');
    }

    public function toggleIsComplete($id)
    {
        $order = Order::findOrFail($id);

        $order->is_complete = ! $order->is_complete;
        $order->save();

        return redirect()->back()->with('message', 'Status uğurla dəyişdirildi.');
    }

    public function updateStatus(Request $request, $orderItemId)
    {
        //        dd($request->all());
        $request->validate([
            'status' => 'required',
        ]);

        $order = $this->orderStatusService->updateStatus($orderItemId, $request->status);
        

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Status uğurla yeniləndi');
    }

     public function updateStatusRedirect(Request $request, $orderItemId)
    {
        //        dd($request->all());
        $request->validate([
            'status' => 'required',
        ]);

        $order = $this->orderStatusService->updateStatus($orderItemId, $request->status);

        return redirect()->back()
            ->with('success', 'Status uğurla yeniləndi');
    }


    

    public function updateAdminStatus(Request $request, $orderItemId)
    {
        $request->validate([
            'admin_status' => 'required',
        ]);

        $adminStatus = Status::find($request->admin_status);
        $frontStatus = $adminStatus->related;


        if($frontStatus){
            OrderItemStatus::create([
                'order_item_id' => $orderItemId,
                'status_id' => $frontStatus->id,
            ]);
        }
       


        // dd($frontStatus,$adminStatus);

        $order = OrderItem::query()->findOrFail($orderItemId);

        $order->update([
            'admin_status' => $request->admin_status,
        ]);

        return redirect()->back()
            ->with('success', 'Admin status uğurla yeniləndi');
    }


    public function refundPayment(Request $request){
              $paymentService = new PaymentService();
              $payment = $paymentService->refundPayment($request->operationId, $request->amount);

              $errors = array_key_exists('Errors', $payment) ? $payment['Errors'] : [];
              $firstError = !empty($errors) ? reset($errors) : null;
              if($firstError){
                return redirect()->back()->with('error', $firstError['message']);
              }

            return redirect()->back()->with('success', "Successfully Operation");
    }
}
