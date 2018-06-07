<?php

namespace App\Http\Controllers;


use App\Link;
use DB;
use Illuminate\Http\Request;



class ParseController extends Controller
{


    public function index()

    {
        $links = DB::table('links')->orderBy('id', 'desc')->paginate(60)->withPath('buhgalterskie_novosti');
        $links_mob = DB::table('links')->orderBy('id', 'desc')->simplePaginate(60)->withPath('buhgalterskie_novosti');

        return view('index')
            ->with(['links_mob' => $links_mob, 'links' => $links]);
    }

    public function show_news($slug)

    {
        $article = Link::where('slug', $slug)->first();
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

    public function test()
    {
        Link::test_phantom();
    }


}
