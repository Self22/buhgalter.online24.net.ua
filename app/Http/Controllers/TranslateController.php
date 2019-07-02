<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\TranslatedArticle;

class TranslateController extends Controller
{
    public function index()

    {
        $links = DB::table('translated_articles')
            ->orderBy('id', 'desc')
            ->paginate(60);

        $links_mob = DB::table('translated_articles')
            ->orderBy('id', 'desc')
            ->simplePaginate(60);

        return view('translated.index')
            ->with(['links_mob' => $links_mob, 'links' => $links]);
    }

    public function show_article($slug)

    {

        if (TranslatedArticle::where('slug', $slug)->exists()) {

            $article = DB::table('translated_articles')->where('slug', $slug)->first();
            $page_title = $article->anchor.'| Buhgalter.Online24';
            $site_name = $article->site;
            $description = $article->description;
            $main_title = $article->anchor;
            $text = $article->text;
            $origin = $article->href;
            $timeDate = $article->created_at;


            return view('translated.news')
                ->with('text',  $text)
                ->with('page_title',  $page_title)
                ->with('description',  $description)
                ->with('main_title',  $main_title)
                ->with('site_name',  $site_name)
                ->with('time',  $timeDate);
        }

        else{
            return response()->view('errors.404', [], 404);
        }
    }

}
