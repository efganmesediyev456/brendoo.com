<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\MobileBanner;
use App\Models\ReturnReason;
use App\Models\SubCategory;
use App\Models\ThirdCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReturnReasonController extends Controller
{
    public function __construct() {
        $this->middleware('permission:list-return_reasons|create-return_reasons|edit-return_reasons', ['only' => ['index','show']]);
        $this->middleware('permission:create-return_reasons', ['only' => ['create','store']]);
        $this->middleware('permission:edit-return_reasons', ['only' => ['edit']]);
    }
    public function index()
    {
        $items = ReturnReason::paginate(10);
        return view('admin.return_reasons.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {
        return view('admin.return_reasons.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'en_title'=>'required',
            'ru_title'=>'required',
        ]);
        ReturnReason::create([
            'en'=>[
                'title'=>$request->en_title,
            ],
            'ru'=>[
                'title'=>$request->ru_title,
            ]
        ]);
        return redirect()->route('return_reasons.index')->with('message','Return reasons added successfully');
    }

    /**
     * Display the specified resource.
     */
   

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($item)
    {
        $item = ReturnReason::findOrFail($item);
        return view("admin.return_reasons.edit", compact("item"));
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request,  $reason_id)
    {
        $request->validate([
            'en_title'=>'required',
            'ru_title'=>'required',
        ]);
        $reason = ReturnReason::find($reason_id);
        $reason->update( [
            'en'=>[
                'title'=>$request->en_title,
            ],
            'ru'=>[
                'title'=>$request->ru_title,
            ]
        ]);
        return redirect()->back()->with('message','Return reasons updated successfully');
    }

    public function destroy($id){
        $returnReason = ReturnReason::find($id);
        $returnReason->delete();
        return redirect()->back()->withSuccess("Uğurla silindi!");
    }
}
