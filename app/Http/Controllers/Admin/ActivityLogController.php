<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductTranslation;
use Illuminate\Http\Request;
use App\Models\Activity;
class ActivityLogController extends Controller
{
  public function index(Request $request)
{
    $query = Activity::orderBy('id', 'desc');

    if ($request->filled('product_code')) {
        $code = $request->product_code;

        $productIds = Product::where('code', 'like', $code . '%')->pluck('id')->toArray();

        $translationIds = ProductTranslation::whereHas('product', function ($q) use ($code) {
            $q->where('code', 'like', $code . '%');
        })->pluck('id')->toArray();

        if (empty($productIds) && empty($translationIds)) {
            return view('admin.logs.index', ['logs' => collect()]);
        }

        $query->where(function ($q) use ($productIds, $translationIds) {
            if (!empty($productIds)) {
                $q->orWhere(function ($q) use ($productIds) {
                    $q->where('subject_type', Product::class)
                        ->whereIn('subject_id', $productIds);
                });
            }

            if (!empty($translationIds)) {
                $q->orWhere(function ($q) use ($translationIds) {
                    $q->where('subject_type', ProductTranslation::class)
                        ->whereIn('subject_id', $translationIds);
                });
            }
        });
    }

    if ($request->filled('log_name')) {
        $query->where('log_name', 'like', $request->log_name . '%'); // Daha sürətli
    }

    if ($request->filled('event')) {
        $query->where('event', $request->event);
    }

    if ($request->filled('causer_id')) {
        $query->where('causer_id', $request->causer_id);
    }

    if ($request->filled('subject_type')) {
        $query->where('subject_type', $request->subject_type);
    }

    if ($request->filled('start_date') && $request->filled('end_date')) {
        $start = $request->start_date . ' 00:00:00';
        $end = $request->end_date . ' 23:59:59';
        $query->whereBetween('created_at', [$start, $end]);
    }

    $logs = $query->paginate(10);

    return view('admin.logs.index', compact('logs'));
}


}
