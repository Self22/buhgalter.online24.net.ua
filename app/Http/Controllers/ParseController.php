<?php

namespace App\Http\Controllers;

use Htmldom;
use App\Link;
use DB;


use naffiq\telegram\channel\Manager;

use Illuminate\Http\Request;
use Carbon\Carbon;


class ParseController extends Controller
{




    public function index()

    {
        $links =  DB::table('links')->orderBy('id', 'desc')->paginate(60);
        $links_mob = DB::table('links')->orderBy('id', 'desc')->simplePaginate(30);
//        $links = $links->sortByDesc('id');
//        $links = $links->paginate(15);s
        return view('index')
            ->with(['links_mob' => $links_mob, 'links' => $links]);
    }







}
