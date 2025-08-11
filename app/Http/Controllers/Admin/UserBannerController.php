<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TopLine;
use App\Models\UserBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserBannerController extends Controller
{
       public function __construct()
        {
        $this->middleware('permission:list-user_banners|edit-user_banners', ['only' => ['index','show']]);
        $this->middleware('permission:edit-user_banners', ['only' => ['edit']]);
        }

    public function index()
    {

        $item = UserBanner::query()->first();
        if(is_null($item)){
            $item = UserBanner::create([]);
        }
        return view('admin.user_banners.index', compact('item'));

    }
    /**
     * Show the form for editing the specified resource.
     */
  

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, UserBanner $item)
    {
        
        $request->validate([
            'banner'=>'required',
        ]);
        $item = UserBanner::first();

        if ($request->hasFile('banner')) {
            if ($item->banner) {
                Storage::delete($item->banner);
            }
            $path = $request->file('banner')->store('banners', 'public');
            $item->update([
                'banner' => $path,
            ]);
        }

        return redirect()->back()->with('message','Banner updated successfully');

    }
}
