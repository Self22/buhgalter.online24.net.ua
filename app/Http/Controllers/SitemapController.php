<?php

namespace App\Http\Controllers;
use App\Link;
use DB;
use Carbon;

use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function index()
    {
        $links = DB::table('links')->select('slug', 'created_at')->get();
        $translated = DB::table('translated_articles')->select('slug', 'created_at')->get();

        return response()->view('sitemap.index', [
            'links' => $links,
            'translated' => $translated
        ])->header('Content-Type', 'text/xml');
    }
}
