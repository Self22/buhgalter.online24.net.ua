<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sitemap\SitemapGenerator;

class SiteSettings extends Model
{
    public static function createSitemap(){
        ini_set('max_execution_time', 90000);
        SitemapGenerator::create('https://buhgalter.online24.net.ua/')->writeToFile(public_path('sitemap.xml'));
    }
}
