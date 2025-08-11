<?php
namespace App\Http\Controllers\Influencer;
use App\Enums\DemandPaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\DemandPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;    

class DashboardController extends Controller{
    public function salesStatics(Request $request){

        $influencer = auth("influencers")->user();

        $demandPayments= null;
        $filterAgo = null;
        switch($request->filter){
            case "onemonthago":
                $filterAgo = Carbon::now()->subMonth();
            case "oneWeekAgo":
                $filterAgo = Carbon::now()->subWeek();
            case "threeMonthAgo":
                $filterAgo = Carbon::now()->subMonth(3);
            default:
                $filterAgo = Carbon::now()->subWeek();
        }

        $demandPayments = $influencer->demandPayments()->where('created_at','>=',$filterAgo)->get() ;

        $paymentCollection = $demandPayments->filter(function($item){
            return $item->getRawOriginal('type') == 'collection';
        });

        $couponCollection = $demandPayments->filter(function($item){
            return $item->getRawOriginal('type') == 'coupon';
        });



        if(count($couponCollection) == 0){
            $demandPayments = $demandPayments->prepend(new DemandPayment([
                'influencer_id'=>$influencer->id,
                'type'=>'coupon',
                'amount' => $influencer->typeBalanceValues('coupon')->where('created_at', '>=', $filterAgo)->sum('amount'),
                'status'=> 4,
                'id'=>$demandPayments->max('id') + 1
            ]));
        }


        if(count($paymentCollection) == 0){
            $demandPayments = $demandPayments->prepend(new DemandPayment([
                'influencer_id'=>$influencer->id,
                'type'=>'collection',
                'amount' => $influencer->typeBalanceValues('collection')->where('created_at', '>=', $filterAgo)->sum('amount'),
                'status'=> 4,
                'id'=>$demandPayments->max('id') + 1
            ]));
        }

        $statusLists = collect([DemandPaymentStatusEnum::Paid,DemandPaymentStatusEnum::Pending, DemandPaymentStatusEnum::NotRequested])->map(function($status) use($demandPayments){
            
            $array = $demandPayments->filter(function($payment) use($status){
                    return $payment->status->value==$status->value;
                });
            return [
                $status->label()=>[
                    "id"=> $status->value,
                    "name"=>$status->label(),
                    "total_amount"=>collect($array)->sum("amount"),
                    "items_count"=>count(($array))
                ]
            ];
        });
        return $this->responseMessage("success","Successfully Process", $statusLists, 200, null);
    }


    public function revenueChart(Request $request){
        $influencer = auth("influencers")->user();
        $demandPayments= null;
      
        $demandPayments = $influencer->demandPayments;
        $now = Carbon::now(); 
        $groupedByWeeks = [];
        if($request->has('filter') and $request->filter=='fourweekago'){
            $groupedByWeeks = collect(range(0, 3))->mapWithKeys(function ($week) use ($demandPayments, $now) {
                    $end = $now->copy()->subWeeks($week);
                    $start = $now->copy()->subWeeks($week + 1);

                    $weekly = $demandPayments->filter(function ($item) use ($start, $end) {
                        return $item->created_at >= $start && $item->created_at < $end;
                    });

                    $label =  ($week + 1) . "w (" . $start->format('d-M') . " - " . $end->format('d-M').')';

                    return [
                        $label => $weekly->values()->sum("amount")
                    ];
            });
        }

        else if($request->has('filter') and $request->filter=='fourweekmonth'){
            $groupedByWeeks = collect(range(0, 3))->mapWithKeys(function ($week) use ($demandPayments, $now) {
                    $end = $now->copy()->subMonth($week);
                    $start = $now->copy()->subMonth($week + 1);

                    $weekly = $demandPayments->filter(function ($item) use ($start, $end) {
                        return $item->created_at >= $start && $item->created_at < $end;
                    });

                    $label =  ($week + 1) . "m (" . $start->format('d-M') . " - " . $end->format('d-M').')';

                    return [
                        $label => $weekly->values()->sum("amount")
                    ];
            });
        }
        

       


      
       
        return $groupedByWeeks;

    }


    public function sourceOfIncome(){
        $influencer = auth("influencers")->user();
        $demandPayments= null;
        $demandPayments = $influencer->demandPayments()->get() ;

        $paymentCollection = $demandPayments->filter(function($item){
            return $item->getRawOriginal('type') == 'collection';
        });

        $couponCollection = $demandPayments->filter(function($item){
            return $item->getRawOriginal('type') == 'coupon';
        });

        $total = $paymentCollection->sum("amount") + $couponCollection->sum("amount");



        $promoSum = $paymentCollection->sum("amount");
        $couponSum = $couponCollection->sum("amount");

        return  [
                    [
                        "amount" => $promoSum,
                        "percent" => $total > 0 ? round(($promoSum / $total) * 100, 2) . '%' : '0%',
                        "title" => "Promokod",
                    ],
                    [
                        "amount" => $couponSum,
                        "percent" => $total > 0 ? round(($couponSum / $total) * 100, 2) . '%' : '0%',
                        "title" => "Paylaşılan link"
                    ],
                ];
    }


    
}