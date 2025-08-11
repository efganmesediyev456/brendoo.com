<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Special;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use App\Models\Status;

class StatusController extends Controller
{
    public function __construct(protected ImageUploadService $imageUploadService)
    {
        $this->middleware('permission:list-statuses|create-statuses|edit-statuses|delete-statuses', ['only' => ['index','show']]);
        $this->middleware('permission:create-statuses', ['only' => ['create','store']]);
        $this->middleware('permission:edit-statuses', ['only' => ['edit']]);
        $this->middleware('permission:delete-statuses', ['only' => ['destroy']]);
    }

    public function index()
    {

        $statuses = Status::orderBy('id','asc')->where('type','!=',2)->paginate(10);
        return view('admin.status.index', compact('statuses'));

    }

    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {

        return view('admin.status.create');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'title_ru'=>'required',
            'title_en'=>'required',
            'type'=>'required',
        ]);

        Status::create([
             'title_ru'=>$request->title_ru,
             'title_en'=>$request->title_en,
             'type'=>$request->type,
             'cancelable'=>$request->cancelable,
             "topdelivery_status_id"=>$request->topdelivery_status_id,
            "related_id"=>$request->related_id

        ]);

        return redirect()->route('statuses.index')->with('message','Status added successfully');

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Status $status)
    {
        $statuses= Status::get();
        return view('admin.status.edit', compact('status', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, Status $status)
    {

        $request->validate([
            'title_ru'=>'required',
            'title_en'=>'required',
            'type'=>'required'
        ]);

       
        $status->update( [
            'title_ru'=> $request->title_ru,
            'title_en'=> $request->title_en,
            'type'=>$request->type,
            'cancelable'=>$request->cancelable,
            'topdelivery_status_id' => $request->topdelivery_status_id,
            "related_id"=>$request->related_id
        ]);

        return redirect()->back()->with('message','Status updated successfully');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Status $status)
    {


        
        $status->delete();
        return redirect()->route('statuses.index')->with('message', 'Status deleted successfully');

    }
}
