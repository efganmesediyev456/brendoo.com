<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
     <sitemap>
        <loc>{{ url('/sitemap-static.xml') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
    </sitemap>
    @for ($i = 1; $i <= $pages; $i++)
        <sitemap>
            <loc>{{ url("/sitemap-products-$i.xml") }}</loc>
            <lastmod>{{ now()->toAtomString() }}</lastmod>
        </sitemap>
    @endfor
</sitemapindex>


