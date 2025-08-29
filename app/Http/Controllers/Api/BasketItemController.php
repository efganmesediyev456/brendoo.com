<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BasketItemResource;
use App\Models\BasketItem;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BasketItemController extends Controller
{

    public function index(): JsonResponse
    {

        $customer = Customer::query()->findOrFail(auth()->user()->id);
        $items = $customer->basketItems()->with('product')->get();

        $totalPrice = $items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $productPriceSum = $items->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        $discount = round($productPriceSum - $totalPrice, 2);

        return response()->json([
            'basket_items' => BasketItemResource::collection($items),
            'total_price' => round($totalPrice + $discount, 2),
            'discount' => $discount,
            'final_price' => round($totalPrice, 2)
        ]);

    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'options' => 'nullable|array',
            'options.*.filter_id' => 'required|exists:filters,id',
            'options.*.option_id' => 'required|exists:options,id',
        ]);

        $customer = Customer::findOrFail(auth()->user()->id);
        $product = Product::findOrFail($validated['product_id']);

        $price = ($product->discount && $product->discount > 0)
            ? $product->discounted_price
            : $product->price;

        $options = $validated['options'] ?? [];

        // Avtomatik seçilə bilən option-ları əlavə et
        $autoOptions = $product->autoSelectableOptions();
        foreach ($autoOptions as $filterId => $item) {
            $existsInRequest = collect($options)->contains('filter_id', $filterId);
            if (!$existsInRequest) {
                $options[] = [
                    'filter_id' => $item->filter_id,
                    'option_id' => $item->option_id,
                ];
            }
        }

        // Mövcud səbət item-lərini yoxla
        $existingItem = $customer->basketItems()->with('options')->get()->first(function ($item) use ($product, $options) {
            if ($item->product_id !== $product->id) {
                return false;
            }

            $existing = $item->options->map(function ($opt) {
                return ['filter_id' => $opt->filter_id, 'option_id' => $opt->option_id];
            })->sortBy('filter_id')->values()->toArray();

            $incoming = collect($options)->sortBy('filter_id')->values()->toArray();

            return $existing == $incoming;
        });

        if ($existingItem) {
            // Əgər eyni kombin tapılıbsa, yenilə
            $existingItem->update([
                'quantity' => $validated['quantity'],
                'price' => $price,
                'collection_id'=>$request->collection_id
            ]);
            $basketItem = $existingItem;
        } else {
            // Yoxdursa yeni əlavə et
            $basketItem = $customer->basketItems()->create([
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'price' => $price,
                'collection_id'=>$request->collection_id
            ]);
        }

        // Options-ları təmizlə və yenidən əlavə et
        $basketItem->options()->delete();
        foreach ($options as $option) {
            $basketItem->options()->create([
                'filter_id' => $option['filter_id'],
                'option_id' => $option['option_id'],
            ]);
        }

        return response()->json(new BasketItemResource($basketItem->load('options')), 201);
    }

    public function update(BasketItem $basketItem, Request $request): JsonResponse
    {

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'options' => 'nullable|array', // Allow updating options
            'options.*.filter_id' => 'required|exists:filters,id',
            'options.*.option_id' => 'required|exists:options,id',
        ]);

        $basketItem->update([
            'quantity' => $validated['quantity'],
            'price' => $validated['price'],
        ]);

        if ($request->options) {
            $basketItem->options()->delete();
            foreach ($request->options as $option) {
                $basketItem->options()->create([
                    'filter_id' => $option['filter_id'],
                    'option_id' => $option['option_id'],
                ]);
            }
        }

        return response()->json(new BasketItemResource($basketItem->load('options')), 200);
    }

    public function destroy(BasketItem $basketItem) : JsonResponse
    {
        $basketItem->delete();
        return response()->json(['message' => 'Item removed']);

    }

    public function storeMultipleBasketItems(Request $request) : JsonResponse
    {

        // dd($request->all());
        try {
            $validated = $request->validate([
                'basket_items' => 'required|array',
                'basket_items.*.product_id' => 'required|exists:products,id',
                'basket_items.*.quantity' => 'required|integer|min:1',
                'basket_items.*.price' => 'required|numeric|min:0',
                'basket_items.*.options' => 'nullable|array',
                'basket_items.*.options.*.filter_id' => 'required|exists:filters,id',
                'basket_items.*.options.*.option_id' => 'required|exists:options,id',
            ]);

            $customer = Customer::query()->findOrFail(auth()->user()->id);

            $basketItems = [];

            foreach ($validated['basket_items'] as $basketItem) {
                $item = $customer->basketItems()->updateOrCreate(
                    ['product_id' => $basketItem['product_id']],
                    ['quantity' => $basketItem['quantity'], 'price' => $basketItem['price']]
                );

                if (!empty($basketItem['options'])) {
                    $item->options()->delete();
                    $optionsData = array_map(fn($option) => [
                        'filter_id' => $option['filter_id'],
                        'option_id' => $option['option_id'],
                    ], $basketItem['options']);

                    $item->options()->createMany($optionsData);
                }

                $basketItems[] = $item->load('options');
            }

            return response()->json(BasketItemResource::collection($basketItems), 201);
        }catch (\Exception $exception){
            return response()->json($exception->getMessage());
        }
    }

}






