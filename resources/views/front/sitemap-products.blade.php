<?xml version="1.0" encoding="UTF-8"?>
<urlset 
    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xhtml="http://www.w3.org/1999/xhtml">

    @foreach($products as $product)
        <url>
            <loc>{{ 'https://brendoo.com/ru/продукт/' . $product->translate('ru')->slug }}</loc>
            <xhtml:link 
                rel="alternate" 
                hreflang="ru" 
                href="https://brendoo.com/ru/продукт/{{ $product->translate('ru')->slug }}" />
            <xhtml:link 
                rel="alternate" 
                hreflang="en" 
                href="https://brendoo.com/en/product/{{ $product->translate('en')->slug }}" />
            <lastmod>{{ $product->updated_at->format('Y-m-d\TH:i:sP') }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>

        <url>
            <loc>{{ 'https://brendoo.com/en/product/' . $product->translate('en')->slug }}</loc>
            <xhtml:link 
                rel="alternate" 
                hreflang="ru" 
                href="https://brendoo.com/ru/продукт/{{ $product->translate('ru')->slug }}" />
            <xhtml:link 
                rel="alternate" 
                hreflang="en" 
                href="https://brendoo.com/en/product/{{ $product->translate('en')->slug }}" />
            <lastmod>{{ $product->updated_at->format('Y-m-d\TH:i:sP') }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach

</urlset>
