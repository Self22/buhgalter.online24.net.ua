<?php

namespace App\Http\Controllers;

use Htmldom;
use App\Link;
use DB;

use TelegramBot\Api\BotApi;

use Illuminate\Http\Request;
use Carbon\Carbon;

class ParseController extends Controller
{
    public function index()
        
    {
        $links =  DB::table('links')->orderBy('id', 'desc')->paginate(30);
//        $links = $links->sortByDesc('id');
//        $links = $links->paginate(15);
        return view('index')
            ->with('links', $links);
    }



    public function parse_911()
    {
        Link::parse_dtkt();


    }

    public static function getDateTimeAttribute()
    {
        setlocale(LC_ALL, 'ru' . '.utf-8', 'ru_RU' . '.utf-8', 'ru', 'ru_RU');

        echo (Carbon::now('Europe/Kiev')->formatLocalized("%d %B, %Y")) . ' ' . (Carbon::now('Europe/Kiev')->format('H:i'));
    }

//    public static function Date()
//    {
//
//        $locale = locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);
//        echo $locale;
////        setlocale(LC_ALL, 'ru' . '.utf-8', 'ru_RU' . '.utf-8', 'ru', 'ru_RU');
////        header('Content-Type: text/html; charset=utf-8');
////        $date = strftime('Число: %d, месяц: %B, день недели: %A, час %H, минута %M');
////        $str = mb_detect_encoding($date);
////        echo($str);
//    }



    public function telegram()
    {


        $bot = new \TelegramBot\Api\BotApi('541854266:AAHestNP3Kw89xumgUk_oS05zC7S1i5z7XI');
        $bot->sendMessage(467775523, 'Чварика');

//
//            $bot->run();
//            echo "run!";

    }

}
