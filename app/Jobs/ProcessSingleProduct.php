<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\PriceCalculatorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ProcessSingleProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $product;
    public $category_id;
    public $sub_category_id;
    public $third_category_id;
    public $brand_id;
    public $user_id;

    public function __construct($product, $category_id, $sub_category_id, $third_category_id, $brand_id, $user_id)
    {
        $this->product          = $product;
        $this->category_id      = $category_id;
        $this->sub_category_id  = $sub_category_id;
        $this->third_category_id= $third_category_id;
        $this->brand_id         = $brand_id;
        $this->user_id          = $user_id;
    }

    public function handle(): void
    {
        if (Product::query()->where('listing_id', $this->product['id'])->exists()) {
            return;
        }

        $calculatorService = new PriceCalculatorService();

        $images = $this->product['images'] ?? [];
        $image = $images['image'] ?? null;
        if (is_array($image)) {
            $image = $image[0];
        }

        $new_product = Product::create([
            'user_id'           => $this->user_id,
            'listing_id'        => $this->product['id'],
            'url'               => $this->product['url'],
            'price'             => $calculatorService::calculate($this->product['price']['sellingPrice'] ?? 0),
            'tr_price'          => $this->product['price']['sellingPrice']    ?? 0,
            'discounted_price'  => $this->product['price']['discountedPrice'] ?? 0,
            'image'             => $image,
            'category_id'       => $this->category_id,
            'sub_category_id'   => $this->sub_category_id,
            'third_category_id' => $this->third_category_id,
            'brand_id'          => $this->brand_id,
            'is_active'         => false,
            'en'                => [
                'title'     => $this->product['name'],
                'img_alt'   => $this->product['imageAlt'] ?? '',
                'img_title' => $this->product['imageAlt'] ?? '',
                'slug'      => Str::slug($this->product['name']) . '-en',
            ],
            'ru' => [
                'locale'    => 'ru',
                'title'     => $this->product['name'],
                'img_alt'   => $this->product['imageAlt'] ?? '',
                'img_title' => $this->product['imageAlt'] ?? '',
                'slug'      => Str::slug($this->product['name']) . '-ru',
            ],
        ]);

        if (is_array($images) && isset($images['image'])) {
            $imageList = $images['image'];
            if (is_string($imageList)) {
                $imageList = [$imageList];
            }

            foreach ($imageList as $img) {
                $new_product->sliders()->create([
                    'image' => $img,
                ]);
            }
        }
    }
}
