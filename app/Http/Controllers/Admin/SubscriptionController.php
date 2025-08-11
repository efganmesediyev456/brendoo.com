<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;

class SubscriptionController extends Controller
{

        public function __construct()
    {
        $this->middleware('permission:list-subscriptions|create-subscriptions|edit-subscriptions|delete-subscriptions', ['only' => ['index','show']]);
        $this->middleware('permission:create-subscriptions', ['only' => ['create','store']]);
        $this->middleware('permission:edit-subscriptions', ['only' => ['edit']]);
        $this->middleware('permission:delete-subscriptions', ['only' => ['destroy']]);
    }


    public function index()
    {

        $subscriptions = Subscription::query()->orderByDesc('id')->paginate(10);
        return view('admin.subscriptions.index', compact('subscriptions'));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription)
    {

        $subscription->delete();
        return redirect()->route('subscriptions.index')->with('message', 'Subscription deleted successfully');

    }

}
