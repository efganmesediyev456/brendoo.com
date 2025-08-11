<?xml version="1.0" encoding="UTF-8"?>
<urlset 
    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    @php 
        $url = 'https://brendoo.com';
        $now = now()->format('Y-m-d\TH:i:sP');
    @endphp

    <!-- Ana səhifələr -->
    <url>
        <loc>{{ $url }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <url>
        <loc>{{ $url . '/en' }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <url>
        <loc>{{ $url . '/ru' }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Haqqımızda / About / О нас -->
    <url>
        <loc>{{ $url . '/ru/онас' }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>{{ $url . '/en/about' }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Əlaqə / Contact / Контакт -->
    <url>
        <loc>{{ $url . '/en/contact' }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>

    <url>
        <loc>{{ $url . '/ru/контакт' }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>


    <!-- Brendlər -->
    <url>
        <loc>{{ $url . '/en/brends' }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>

    <url>
        <loc>{{ $url . '/ru/бренды' }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>

    @foreach(['ru','en'] as $locale)
    @foreach($pages as $page)
     <url>
        <loc>{{ $url . '/i/'.$locale.'/'.$page->translate($locale)->slug }}</loc>
        <lastmod>{{ $now }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach
    @endforeach

    

</urlset>
