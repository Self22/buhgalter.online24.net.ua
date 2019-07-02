<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($links as $link)
        <url>
            <loc>https://buhgalter.online24.net.ua/buhgalterskaya_novost/{{ $link->slug }}</loc>
            <lastmod>{{ date('Y-m-d', strtotime($link->created_at)) }}</lastmod>
            <priority>0.8</priority>
        </url>
    @endforeach

    @foreach ($translated as $translate)
        <url>
            <loc>https://buhgalter.online24.net.ua/buhgalterskaya_consultacia/{{ $translate->slug }}</loc>
            <lastmod>{{ date('Y-m-d', strtotime($translate->created_at)) }}</lastmod>
            <priority>0.8</priority>
        </url>
    @endforeach
        <url>
            <loc>https://buhgalter.online24.net.ua/sourse/buhgalter_ua</loc>
            <lastmod>2019-03-19</lastmod>
            <priority>0.9</priority>
        </url>
        <url>
            <loc>https://buhgalter.online24.net.ua/sourse/balanse</loc>
            <lastmod>2019-03-19</lastmod>
            <priority>0.9</priority>
        </url>
        <url>
            <loc>https://buhgalter.online24.net.ua/sourse/interaktivnaya_buhgalteriya</loc>
            <lastmod>2019-03-19</lastmod>
            <priority>0.9</priority>
        </url>
        <url>
            <loc>https://buhgalter.online24.net.ua/sourse/buhgalter911</loc>
            <lastmod>2019-03-19</lastmod>
            <priority>0.9</priority>
        </url>
        <url>
            <loc>https://buhgalter.online24.net.ua/sourse/ifactor</loc>
            <lastmod>2019-03-19</lastmod>
            <priority>0.9</priority>
        </url>
        <url>
            <loc>https://buhgalter.online24.net.ua/sourse/debet_kredit</loc>
            <lastmod>2019-03-19</lastmod>
            <priority>0.9</priority>
        </url>
</urlset>