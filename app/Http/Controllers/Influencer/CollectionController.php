<?php

namespace App\Http\Controllers\Influencer;

use App\Enums\DemandPaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\BalanceResource;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\CouponResource;
use App\Http\Resources\DemandPaymentResource;
use App\Http\Resources\HomeProductResource;
use App\Http\Resources\ProductResource;
use App\Models\Collection;
use App\Models\Coupon;
use App\Models\DemandPayment;
use App\Models\Influencer;
use App\Models\InfluencerCollectionProduct;
use App\Models\Product;
use App\Models\Status;
use Exception;
use Illuminate\Http\Request;
use DB;
use Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;


class CollectionController extends Controller
{

    public function index(Request $request)
    {
        $influencer = auth('influencers')->user();
        $collections = $influencer->collections()->orderBy("id", 'desc');
        if ($request->filled('search')) {
            $search = $request->get('search');
            $collections = $collections->where('title', "like", "%$search%")->orWhere("description", "like", "%$search%");
        }
        $collections = $collections->get();
        return CollectionResource::collection($collections);
    }
    public function store(Request $request)
    {
        $this->validate($request, [
            "title" => "required"
        ]);
        try {

            DB::beginTransaction();

            $influencer = auth('influencers')->user();
            $collection = $influencer->collections()->create($request->except("_token"));
            DB::commit();


            return $this->responseMessage("success", "Successfully Created", $collection, 200, null);


        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseMessage("error", $e->getMessage(), null, 500, null);
        }

    }


    public function update(Request $request, $collectionId)
    {

        $this->validate($request, [
            "title" => "required",
        ]);

        try {
            DB::beginTransaction();
            $collection = Collection::find($collectionId);
            if (is_null($collection)) {
                return $this->responseMessage("error", "No found Collection", null, 400, null);
            }
            $collection->update($request->except("_token"));
            DB::commit();
            return $this->responseMessage("success", "Successfully Updated", $collection->refresh(), 200, null);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseMessage("error", $e->getMessage(), null, 500, null);
        }
    }


    public function addProduct(Request $request)
    {
        try {
            DB::beginTransaction();

            $this->validate($request, [
                "product_id" => "required|exists:products,id",
                "collection_id" => "required|array",
            ]);

            $influencer = Auth::guard("influencers")->user();

            foreach ($request->collection_id as $collection_id) {
                $influencer->products()->updateOrCreate([
                    "collection_id" => $collection_id,
                    "product_id" => $request->product_id
                ], [
                    "collection_id" => $collection_id,
                    "product_id" => $request->product_id
                ]);
            }

            DB::commit();
            return $this->responseMessage("success", "Successfully Added", null, 200, null);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseMessage("error", $e->getMessage(), null, 500, null);
        }
    }


    public function removeProduct(Request $request)
    {
        $this->validate($request, [
            "product_id" => "required|exists:products,id",
            "collection_id" => "required|exists:collections,id",
        ]);
        try {

            DB::beginTransaction();
            $collection = Collection::find($request->collection_id);
            $collection->products()->detach($request->product_id);
            DB::commit();

            return $this->responseMessage("success", "Successfully Deleted", null, 200, null);

        } catch (\Exception $e) {
            return $this->responseMessage("error", $e->getMessage(), null, 500, null);
        }
    }


    public function show(Request $request, $collectionId)
    {
        try {

            $collection = Collection::find($collectionId);
            if (is_null($collection)) {
                return $this->responseMessage("error", "No found Collection", null, 400, null);
            }
            $productIds = auth()->guard('influencers')->user()->collectionProducts($collectionId)->pluck('product_id')->toArray();
            $products = Product::whereIn('id', $productIds)->get();



            $data = [
                "collection" => new CollectionResource($collection),
                "products" => HomeProductResource::collection($products)
            ];

            return $this->responseMessage("success", "Successfully Process", $data, 200, null);

        } catch (\Exception $e) {
            return $this->responseMessage("error", $e->getMessage(), null, 500, null);
        }
    }

    public function demandPayments(Request $request)
    {
        $influencer = Auth::guard("influencers")->user();

        $demandPayments = $influencer->demandPayments->where("status", DemandPaymentStatusEnum::NotRequested);

        // $collectionPaymentValue = $influencer->typeBalancesValue("collection")?->balance;
        // $couponPaymentValue = $influencer->typeBalancesValue("coupon")?->balance;


        // odenis 14 gunu kecibse



        $paymentTypes = $demandPayments->map(function ($item) {
            $returnStatus = [6, 14, 15, 16];

            $allItemsMatch = $item->orderItems->filter(function ($orderItem) use ($returnStatus) {
                return $orderItem->created_at->diffInDays(now()) >= 14
                    && !in_array($orderItem->orderItem?->lastStatus?->status?->id, $returnStatus);
            });

            if ($allItemsMatch->isNotEmpty()) {
                $amount = $allItemsMatch->sum('amount');
                return [
                    "type" => $item->id,
                    "amount" => $amount
                ];
            }

            return null;
        })->filter()->values();




        return $this->responseMessage('success', 'SuccessFully Operation', [
            "paymentTypes" => $paymentTypes,
            "totalBalance" => $paymentTypes->sum('amount')
        ], 200, null);
    }


    public function createDemandPayment(Request $request)
    {
        $this->validate($request, [
            "type" => "required|array",
            "type.*" => "required"
        ]);

        try {
            $influencer = auth()->guard("influencers")->user();

            // $types = [
            //     "collectionPaymentValue" => "collection",
            //     "couponPaymentValue" => "coupon"
            // ];

            $responses = [];

            foreach ($request->type as $inputType) {
                // if (!array_key_exists($inputType, $types)) {
                //     throw new \Exception("Növ düzgün deyil: {$inputType}");
                // }

                


                // $type = $types[$inputType];

                $demandPayment = DemandPayment::find($inputType);

                if (!$demandPayment) {
                    throw new \Exception("Ödəniş tapılmadı: {$inputType}");
                }


                // $existingPending = $influencer->demandPayments
                //     ->where('type', $type)
                //     ->where('status', DemandPaymentStatusEnum::Pending);

                // if ($existingPending->count()) {
                //     throw new \Exception("{$type} üçün ödəniş tələbiniz gözlənilir. Zəhmət olmasa gözləyin");
                // }


               


                // $balanceObject = $influencer->typeBalancesValue($demandPayment->type);
                // if (!$balanceObject || $balanceObject->balance == 0) {
                //     throw new \Exception("{$demandPayment->amount} rubl üçün balansınızda kifayət qədər məbləğ yoxdur");
                // }

                $balanceObjectClone = new DemandPayment();

                $returnStatus = [6, 14, 15, 16];


                $allItemsMatch = $demandPayment->orderItems->filter(function ($orderItem) use ($returnStatus) {
                    return $orderItem->created_at->diffInDays(now()) >= 14
                        && !in_array($orderItem->orderItem?->lastStatus?->status?->id, $returnStatus);
                });
                $amount = 0;

                if ($allItemsMatch->isNotEmpty()) {
                    $amount = $allItemsMatch->sum('amount');
                    if ($amount != $demandPayment->amount) {
                        $balanceObjectClone->amount = $amount;



                        $balanceObjectClone->status = DemandPaymentStatusEnum::Pending;
                        $balanceObjectClone->type = $demandPayment->type;
                        $balanceObjectClone->influencer_id = $demandPayment->influencer_id;

                        $balanceObjectClone->save();
                        $balanceObjectClone = $balanceObjectClone->fresh();

                        $allItemsMatch->every(function ($allItem) use ($balanceObjectClone) {
                            $allItem->demand_payment_balance_id = $balanceObjectClone->id;
                            $allItem->save();
                        });

                        $responses[] = $balanceObjectClone;

                        $demandPayment->amount = $demandPayment->amount - $amount;
                        $demandPayment->save();

                    } else {
                        $demandPayment->status = DemandPaymentStatusEnum::Pending;
                        $demandPayment->save();
                        $responses[] = $demandPayment;
                    }
                }else{
                    throw new \Exception("Ödəniş üçün uyğun məhsul tapılmadı. Zəhmət olmasa 14 gün gözləyin və yenidən cəhd edin.");
                }



            }

            return $this->responseMessage("success", "Successfully Processed", $responses, 200, null);

        } catch (\Exception $e) {
            return $this->responseMessage("error", $e->getMessage(), null, 500, null);
        }
    }



    public function demandPaymentList(Request $request)
    {
        if ($locale = $request->header('Accept-Language') and strlen($locale) === 2) {
            app()->setLocale($locale);
        }

        $influencer = auth("influencers")->user();
        
        return DemandPaymentResource::collection($influencer->demandPayments->sortByDesc("id"));
    }


    public function demandPaymentListDetail($id)
    {
        $influencer = auth("influencers")->user();

        $demandPaymentList = DemandPayment::find($id);
        $balances = $demandPaymentList->orderItems;


        

        // $balances = $influencer->balances()->where('type','IN')->where('balance_type', $demandPaymentList->type)->orderBy('id','desc')->get();
        return BalanceResource::collection($balances);
    }

    public function demandPayment(Request $request, $demandPayment)
    {
        $demandPayment = DemandPayment::findOrFail($demandPayment);

        $item = [
            'no' => $demandPayment->id,
            'influser' => 'Efqan',
            'tip' => $demandPayment->type,
            'miqdar' => $demandPayment->amount,
            'status' => $demandPayment->status->label(),
            'tarix' => $demandPayment->created_at->format("Y-m-d H:i:s"),
            'emeliyyat' => '',
        ];

        $pdf = Pdf::loadView('pdf.invoice', compact('item'));

        return $pdf->stream('invoice.pdf');
    }



    public function destroy(Request $request, $collection)
    {
        try {
            $collection = Collection::find($collection);
            if (is_null($collection)) {
                throw new Exception("No found collection");
            }
            ;
            $collection->delete();
            return $this->responseMessage("success", "SuccessFully Deleted", null, 200, null);

        } catch (\Exception $e) {
            return $this->responseMessage("error", "Error Message " . $e->getMessage(), null, 500, null);
        }
    }
}
