<?php

namespace App\Http\Controllers;

use App\Link;
use Illuminate\Http\Request;
use App\Anchor;
use DB;

class SearchController extends Controller
{
    public function commonSearch(Request $request){

        $request = '%'.$request->input('q').'%';

        $links = DB::table('links')
            ->where('anchor', 'like', $request)
            ->orderBy('id', 'desc')
            ->paginate(60)
            ->withPath('buhgalterskie_novosti');

        $links_mob = DB::table('links')
            ->where('anchor', 'like', $request)
            ->orderBy('id', 'desc')
            ->simplePaginate(60)
            ->withPath('buhgalterskie_novosti');

        return view('index')
            ->with(['links_mob' => $links_mob, 'links' => $links]);

        // the code below is for Algolia Search

//        $articles =  Anchor::search($request->input('q'))->get();
//
//        $links = $articles->sortByDesc('created_at');

//        return view('search')
//            ->with('links', $links);
    }

    public function sourseSearch($slug){

        $site = '';

        if($slug =='buhgalter_ua'){
            $site = 'Бухгалтер.UA';
        }
        elseif ($slug =='balanse'){
            $site = 'Баланс';
        }
        elseif ($slug =='interaktivnaya_buhgalteriya'){
            $site = 'Интерактивная бухгалтерия';
        }
        elseif ($slug =='buhgalter911'){
            $site = 'Бухгалтер911';
        }
        elseif ($slug =='ifactor'){
            $site = 'iFactor';
        }
        elseif ($slug =='debet_kredit'){
            $site = 'Дебет-Кредит';
        }

        $links = DB::table('links')
            ->where('site', $site)
            ->orderBy('id', 'desc')
            ->paginate(60)
            ->withPath('buhgalterskie_novosti');

        $links_mob = DB::table('links')
            ->where('site', $site)
            ->orderBy('id', 'desc')
            ->simplePaginate(60)
            ->withPath('buhgalterskie_novosti');

        return view('index')
            ->with(['links_mob' => $links_mob, 'links' => $links]);

    }
}

