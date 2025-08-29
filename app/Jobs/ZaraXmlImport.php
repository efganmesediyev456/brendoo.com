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

class ZaraXmlImport implements ShouldQueue
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
        // $fullPath = storage_path('app/' . $this->filePath);
        $fullPath = public_path('storage/'.$this->filePath);

        if ( ! file_exists($fullPath)) {
            Log::error('XML faylı tapılmadı: ' . $fullPath);

            return;
        }

        $calculatorService = new PriceCalculatorService();
        $xmlContent        = file_get_contents($fullPath);
        $xml               = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json              = json_decode(json_encode($xml), true);
        $products          = $json['Urun'] ?? [];

        foreach ($products as $product) {
            if (Product::query()->where('url', $product['URL'])->exists()) {
                continue;
            }
            $images = $product['Resimler'] ?? [];

            $imageMain = $images['AnaResim'] ?? null;




            if (empty($imageMain) && !empty($images)) {
                $allImages = collect($images)->toArray()['DigerResim'] ?? [];
                $firstImage = collect($allImages)
                    ->filter(fn($url) => is_string($url))
                    ->first();
                $imageMain = $firstImage ?: null;
            }


           




            $new_product = Product::create([
                'user_id'    => $this->user_id,
                'listing_id' => array_key_exists('id',$product) ? $product['id'] : null,
                'url'        => $product['URL'],
                'price'      => $calculatorService::calculate(
                    $calculatorService::parsePrice($product['Fiyat'] ?? 0)
                ),
                'tr_price'          => $calculatorService::parsePrice($product['Fiyat'] ?? 0),
                'image'             => $imageMain ?? null,
                'category_id'       => $this->category_id,
                'sub_category_id'   => $this->sub_category_id,
                'third_category_id' => $this->third_category_id,
                'brand_id'          => $this->brand_id,
                'is_active'         => false,
                'en'                => [
                    'title'     => $product['UrunAdi'],
                    'img_alt'   => $product['UrunAdi'] ?? '',
                    'img_title' => $product['UrunAdi'] ?? '',
                    'slug'      => $this->generateUniqueSlug($product['UrunAdi']) . '-en',
                ],
                'ru' => [
                    'title'     => $product['UrunAdi'],
                    'img_alt'   => $product['UrunAdi'] ?? '',
                    'img_title' => $product['UrunAdi'] ?? '',
                    'slug'      => $this->generateUniqueSlug($product['UrunAdi']) . '-ru',
                ],
            ]);

            $images = $product['Resimler'] ?? [];


            

            if (is_array($images)) {
                $imageList = array_values(array_values($images));
                $imageList = collect($images)->flatten()->toArray();
                $imageList = array_filter($imageList, fn ($url) => is_string($url) && ! str_contains($url, 'transparent-background'));

                $imageList = array_filter($imageList, fn ($url) => is_string($url) && ! str_contains($url, 'transparent-background'));

               
                foreach ($imageList as $img) {
                    $new_product->sliders()->create(['image' => $img]);
                }
            }
        }

        // İsteğe bağlı: faylı sil
        unlink($fullPath);
    }
}
