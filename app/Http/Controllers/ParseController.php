<?php

namespace App\Http\Controllers;

use Htmldom;
use App\Link;
use DB;
use Mail;
use App\Mail\SupportExceptionEmail;


use naffiq\telegram\channel\Manager;

use Illuminate\Http\Request;
use Carbon\Carbon;


class ParseController extends Controller
{


    public function index()

    {
        $links = DB::table('links')->orderBy('id', 'desc')->paginate(60)->withPath('buhgalterskie_novosti');
        $links_mob = DB::table('links')->orderBy('id', 'desc')->simplePaginate(60)->withPath('buhgalterskie_novosti');

        return view('index')
            ->with(['links_mob' => $links_mob, 'links' => $links]);
    }

    public function test()
    {
        Link::parse_ib();
    }


}
