<?php

namespace App\Http\Controllers;


use App\ForeignText;
use App\Link;
use DB;
use Illuminate\Http\Request;
use App\SiteSettings;
use App\TranslatedArticle;



class ParseController extends Controller
{


    public function index()

    {
        $links = DB::table('links')
            ->orderBy('id', 'desc')
            ->paginate(60);

        $links_mob = DB::table('links')
            ->orderBy('id', 'desc')
            ->simplePaginate(60);

        return view('index')
            ->with(['links_mob' => $links_mob, 'links' => $links]);
    }

    public function show_news($slug)

    {

        if (Link::where('slug', $slug)->exists()) {

            $article = DB::table('links')->where('slug', $slug)->first();
            $page_title = $article->anchor.'| Buhgalter.Online24';
            $site_name = $article->site;
            $description = $article->description;
            $main_title = $article->anchor;
            $text = $article->news_text;
            $origin = $article->href;
            $time = $article->time;
            $date = $article->date;


            return view('news')
                ->with('text',  $text)
                ->with('page_title',  $page_title)
                ->with('description',  $description)
                ->with('main_title',  $main_title)
                ->with('origin',  $origin)
                ->with('site_name',  $site_name)
                ->with('time',  $time)
                ->with('date',  $date);
        }

        else{
            return response()->view('errors.404', [], 404);
        }
    }

    public function sitemap(){
        SiteSettings::createSitemap();
    }

    public function test()
    {
        Link::parse_911();
        Link::parse_balance();
        Link::parse_buhligazakon_analytics();
        Link::parse_buhligazakon_news();
        Link::parse_ifactor_news();
        Link::parse_dtkt_news();
    }

    public function testParse(){
        Link::testParseResults();
    }

    public function export(){
        Link::exportSql();
    }

    public function testTranslate(){
        TranslatedArticle::makeTranslateArticle();
    }

    public function testUSA(){
        ForeignText::parseJournalOfAccountancy();
    }


}
