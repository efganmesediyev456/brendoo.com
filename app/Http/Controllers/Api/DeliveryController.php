<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RuleResource;
use App\Models\Delivery;
use App\Models\Refund;
use App\Models\Rule;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{

    public function pages(Request $request)
    {
        if ($request->has('page_id')) {
            $page = Rule::find($request->page_id);
            $data = [
                'id' => $page->id,
                'title' => $page->title,
                'description' => $page->description,
                'slug' =>
                    [
                        'en' => $page->translate('en')->slug,
                        'ru' => $page->translate('ru')->slug,
                    ],
            ];
            if($page->id==13){
                $data['image1']=url('storage/'.$page->image1);
                $data['image2']=url('storage/'.$page->image2);
            }


            return $data;
        }
        $pages = Rule::active()->get();
        $result = [];
        foreach ($pages as $page) {
            if ($page->id == 13) {
                continue;
            }
            $key = 'page_' . $page->id;
            $result[$key] = new RuleResource($page);
        }
        return response()->json($result);
    }

}
