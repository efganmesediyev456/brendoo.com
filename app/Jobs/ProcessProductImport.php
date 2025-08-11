<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\PriceCalculatorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessProductImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $filePath;

    public $category_id;

    public $sub_category_id;

    public $third_category_id;

    public $brand_id;

    public $user_id;

    public function __construct(
        $filePath,
        $category_id,
        $sub_category_id,
        $third_category_id,
        $brand_id,
        $user_id,
    ) {
        $this->filePath          = $filePath;
        $this->category_id       = $category_id;
        $this->sub_category_id   = $sub_category_id;
        $this->third_category_id = $third_category_id;
        $this->brand_id          = $brand_id;
        $this->user_id           = $user_id;
    }

    public function generateUniqueSlug($title, $itemId = null): string
    {
        $slug = Str::slug($title);

        if ($itemId) {
            $count = Product::query()->whereNot('id', $itemId)->whereTranslation('title', $title)->count();
        } else {
            $count = Product::query()->whereTranslation('title', $title)->count();
        }

        if ($count > 0) {
            $slug .= '-' . $count;
        }

        return $slug;
    }

    public function handle(): void
    {
        $fullPath = storage_path('app/' . $this->filePath);

        if ( ! file_exists($fullPath)) {
            Log::error('XML faylı tapılmadı: ' . $fullPath);

            return;
        }

        $calculatorService = new PriceCalculatorService();
        $xmlContent        = file_get_contents($fullPath);
        $xml               = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json              = json_decode(json_encode($xml), true);
        $products          = $json['Product'] ?? [];

        foreach ($products as $product) {
            if (Product::query()->where('listing_id', $product['id'])->exists()) {
                continue;
            }

            $images = $product['images'] ?? [];

            $image = $images['image'] ?? null;
            if (is_array($image)) {
                $image = $image[0];
            }

            $new_product = Product::create([
                'user_id'           => $this->user_id,
                'listing_id'        => $product['id'],
                'url'               => $product['url'],
                'price'             => $calculatorService::calculate($product['price']['sellingPrice'] ?? 0),
                'tr_price'          => $product['price']['sellingPrice']    ?? 0,
                'discounted_price'  => $product['price']['discountedPrice'] ?? 0,
                'image'             => $image,
                'category_id'       => $this->category_id,
                'sub_category_id'   => $this->sub_category_id,
                'third_category_id' => $this->third_category_id,
                'brand_id'          => $this->brand_id,
                'is_active'         => false,
                'en'                => [
                    'title'     => $product['name'],
                    'img_alt'   => $product['imageAlt'] ?? '',
                    'img_title' => $product['imageAlt'] ?? '',
                    'slug'      => $this->generateUniqueSlug($product['name']) . '-en',
                ],
                'ru' => [
                    'locale'    => 'ru',
                    'title'     => $product['name'],
                    'img_alt'   => $product['imageAlt'] ?? '',
                    'img_title' => $product['imageAlt'] ?? '',
                    'slug'      => $this->generateUniqueSlug($product['name']) . '-ru',
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


        // İsteğe bağlı: faylı sil
        unlink($fullPath);
    }
}
