<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Enums\AdminOrderStatus;
use App\Http\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Models\Package;
use App\Models\Status;
use App\Services\OrderStatusService;
use App\Services\TopDeliveryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SoapClient;

class PackageController extends Controller
{
    public function __construct(protected OrderStatusService $orderStatusService) {}

    public function index()
    {
        $query = Package::query()->with('orderItems');

        if (request()->filled('barcode')) {
            $query->where('barcode', 'like', '%' . request('barcode') . '%');
        }

        if (request()->filled('tr_barcode')) {
            $query->where('tr_barcode', 'like', '%' . request('tr_barcode') . '%');
        }

        if (request()->filled('start_date')) {
            $query->whereDate('created_at', '>=', request('start_date'));
        }

        if(request()->filled('customer_id')){
            $query->whereHas('orderItems', function ($q) {
               $q->where('customer_id', request()->customer_id);
            });
        }

        if (request()->filled('end_date')) {
            $query->whereDate('created_at', '<=', request('end_date'));
        }

        $packages = $query->latest()->paginate(50);

        return view('admin.packages.index', compact('packages'));
    }

    public function store(Request $request, TopDeliveryService $deliveryService)
    {
        try {
            // Request validation
            $request->validate([
                'order_item_ids'   => 'required|array',
                'order_item_ids.*' => 'exists:order_items,id',
                'weight'           => 'nullable|numeric',
                'tr_barcode'       => 'required|max:255',
                'note'             => 'nullable',
            ]);
            
            if(is_null($request->customer_id)){
                throw new Exception("Müştəri kodu daxil edin");
            }


            // Fetch order items
            $orderItems = OrderItem::with('customer', 'product.sub_category', 'product')
                ->whereIn('id', $request->order_item_ids)->get();

            $productsWithCategories = $orderItems->map(fn ($item) => [
                'product'  => $item->product?->title,
                'category' => $item->product?->sub_category?->title,
            ]);

            $customer = $orderItems->first()->order->customer ?? null;
            $order    = $orderItems->first()->order;

            // Prepare items for API
            $soapItems = $orderItems->map(fn ($item) => [
                'name'          => $item->product?->title ?? 'Unknown Product',
                'article'       => $item->product?->id    ?? 'N/A',
                'count'         => $item->quantity,
                'push'          => $item->quantity,
                'declaredPrice' => $item->price,
                'clientPrice'   => $item->price,
                'weight'        => 100,
                'vat'           => 18,
            ])->toArray();

            $customerInfo = [
                'name'  => $customer ? $customer->name . ' ' . $customer->surname : 'Unknown Customer',
                'phone' => $customer->phone ?? '0000000000',
            ];

            $orderNumber =  uniqid();

            // Prepare and send order data to API
            $orderData = $deliveryService->prepareOrderData(
                $soapItems,
                $request->weight,
                $customerInfo,
                $orderNumber,
                $order
            );



            $soapClient = new SoapClient('https://is-test.topdelivery.ru/api/soap/w/2.0/?WSDL', [
                'trace' => 1,  
                'exceptions' => true,
                'login'=>'tdsoap',
                'password'=>'5f3b5023270883afb9ead456c8985ba8'
            ]);

            $orderData = [
                'auth' => [
                    'login' => 'webshop',
                    'password' => 'pass'
                ],
                'addedOrders' =>  [0=>$orderData]
            ];


            $data = $soapClient->addOrders($orderData);
            if($data->requestResult->status!=0) {
                throw new Exception($data->requestResult->message.' '.$data->addOrdersResult?->message);
            }
            
            $data = json_decode(json_encode($data), true)['addOrdersResult']['orderIdentity'];


         
           

            // Check if barcode is returned successfully
            if ($data['barcode']) {
                // If the barcode exists, create the package
                $package = Package::create([
                    'tr_barcode'            => $request->tr_barcode,
                    'weight'                => $request->weight,
                    'note'                  => $request->note,
                    'status'                => 'pending',
                    'barcode'               => $data['barcode'],
                    'top_delivery_order_id' => $data['orderId'],
                    'webshop_number' => $orderNumber,
                ]);

                $package->orderItems()->attach($request->order_item_ids);

                $turkishOfficeAdmin = new Status();
                $turkishOfficeAdmin = $turkishOfficeAdmin->turkishOfficeAdmin();


                $turkishOffice = new Status();
                $turkishOffice = $turkishOffice->turkishOffice();


                foreach ($orderItems as $item) {
                    $item->update([
                        'admin_status' => $turkishOfficeAdmin->id,
                    ]);

                    $item->statuses()->create([
                        'status_id'=>$turkishOffice->id
                    ]);
                }

                // dd($data['orderId'],
                //     $data['barcode'],
                //     $orderNumber);

                $reportUrl = $deliveryService->printOrderAct(
                    $data['orderId'],
                    $data['barcode'],
                    $orderNumber
                );
                
                $package->update([
                    'topdelivery_waybill_path' => $reportUrl,
                ]);


                return redirect()->to($reportUrl);
            }
            throw new Exception('Failed to create order or get barcode from API');
        } catch (\Exception $exception) {
            Log::error('Error creating order: ' . $exception->getMessage());

            // return $exception->getMessage();

            return redirect()->back()->withError($exception->getMessage());
        }
    }

    //    public function boxify(Request $request)
    //    {
    //        $request->validate([
    //            'package_ids' => 'required|array',
    //            'package_ids.*' => 'exists:packages,id',
    //        ]);
    //
    //        $packages = OrderItem::whereIn('id', $request->package_ids)->get();
    //
    //        foreach ($packages as $package) {
    //            $package->update([
    //                'status' => OrderStatus::Prepared,
    //                'admin_status' => AdminOrderStatus::TurkishOffice,
    //            ]);
    //        }
    //
    //        return redirect()->back()->with('message', 'Seçilmiş paketlər uğurla qutulaşdırıldı ✅');
    //    }

    // PackageController.php
    public function checkBarcode(Request $request)
    {
        $package = Package::where('barcode', $request->barcode)->with('boxes')->first();

        if ($package) {
            if ($package->boxes->isNotEmpty()) {
                return response()->json([
                    'exists'        => true,
                    'weight'        => $package->weight,
                    'already_boxed' => true,
                ]);
            }

            return response()->json([
                'exists'        => true,
                'weight'        => $package->weight,
                'already_boxed' => false,
            ]);
        }

        return response()->json([
            'exists' => false,
        ]);
    }

    public function getStatusDelivery(Request $request, TopDeliveryService $deliveryService)
    {
        $validatedData = $request->validate([
            'top_delivery_order_id' => 'required|string',
            'barcode' => 'required|string',
            'webshop_number' => 'required|string',
        ]);
        $response = $deliveryService->getOrdersInfo([
            'orderId' => $validatedData['top_delivery_order_id'],
            'barcode' => $validatedData['barcode'],
            'webshopNumber' => $validatedData['webshop_number'],
        ]);

        $package = Package::find($request->package_id);
        $deliveryDate= $response->ordersInfo?->orderInfo?->desiredDateDelivery;
        $date = $deliveryDate?->date;
        $fromInterval = $deliveryDate?->timeInterval?->bTime;
        $toInterval=$deliveryDate?->timeInterval?->eTime;
        return [
            'status'=>$response?->ordersInfo?->orderInfo?->status?->name,
            'barcode'=>$package->barcode,
            'weight'=>$package->weight,
            'note'=>$package->note,
            'order_items_count'=>$package->order_items_count ?? $package->orderItems()->count(),
            'orders'=>$package->orderItems?->map(fn($order)=>$order?->product?->title),
            'deliveryDate'=>$date,
            'fromDeliveryInterval'=>$fromInterval,
            'toDeliveryInterval'=>$toInterval
        ];
    }

}
