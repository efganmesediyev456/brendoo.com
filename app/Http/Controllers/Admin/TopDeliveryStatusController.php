<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TopDeliveryStatus;
use Illuminate\Http\Request;

class TopDeliveryStatusController extends Controller
{
    public function index()
    {
        $statuses = TopDeliveryStatus::paginate(10);
        return view('admin.top_delivery_statuses.index', compact('statuses'));
    }

    public function create()
    {
        return view('admin.top_delivery_statuses.create');
    }

    public function store(Request $request)
    {
      
        $request->validate([
            'status_id' => [
                'required', 
                'array', 
                function($attribute, $value, $fail) {
                    if (count($value) !== count(array_unique($value))) {
                        $fail('Status ID-lər unikal olmalıdır.');
                    }

                    $existingStatuses = TopDeliveryStatus::whereJsonContains('status_id', $value)->first();
                    
                        if ($existingStatuses) {
                            $fail('Bu status ID artıq mövcuddur.');
                        }
                    }
                ],
                'title_en' => 'nullable|string|max:255',
                'title_ru' => 'nullable|string|max:255',
        ]);


        TopDeliveryStatus::create([
            'status_id' => $request->status_id,
            'title_en' => $request->title_en,
            'title_ru' => $request->title_ru
        ]);

        return redirect()->route('top-delivery-statuses.index')
            ->with('message', 'Top Delivery Status created successfully');
    }

    public function edit(TopDeliveryStatus $topDeliveryStatus)
    {
        return view('admin.top_delivery_statuses.edit', compact('topDeliveryStatus'));
    }

    public function update(Request $request, TopDeliveryStatus $topDeliveryStatus)
    {
        
        $request->validate([
        'status_id' => [
            'required', 
            'array', 
            function($attribute, $value, $fail) use ($topDeliveryStatus) {
                if (count($value) !== count(array_unique($value))) {
                    $fail('Status ID-lər unikal olmalıdır.');
                }

                $existingStatuses = TopDeliveryStatus::where('id', '!=', $topDeliveryStatus->id)
                    ->where(function($query) use ($value) {
                        foreach ($value as $statusId) {
                            $query->orWhereJsonContains('status_id', $statusId);
                        }
                    })
                    ->first();
                
                if ($existingStatuses) {
                    $fail('Daxil edilən status ID-lərdən biri artıq mövcuddur.');
                }
            }
        ],
        'title_en' => 'nullable|string|max:255',
        'title_ru' => 'nullable|string|max:255',
    ]);

        $topDeliveryStatus->update([
            'status_id' => $request->status_id,
            'title_en' => $request->title_en,
            'title_ru' => $request->title_ru
        ]);

        return redirect()->route('top-delivery-statuses.index')
            ->with('message', 'Top Delivery Status updated successfully');
    }

    public function destroy(TopDeliveryStatus $topDeliveryStatus)
    {
        $topDeliveryStatus->delete();

        return redirect()->route('top-delivery-statuses.index')
            ->with('message', 'Top Delivery Status deleted successfully');
    }
}
