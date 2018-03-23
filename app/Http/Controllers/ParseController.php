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
        $links =  DB::table('links')->orderBy('id', 'desc')->paginate(30);
//        $links = $links->sortByDesc('id');
//        $links = $links->paginate(15);
        return view('index')
            ->with('links', $links);
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



//    public function telegram()
//    {
//
//        $manager = new \naffiq\telegram\channel\Manager('541854266:AAHestNP3Kw89xumgUk_oS05zC7S1i5z7XI', -1001195518704);
//
//        $manager->postMessage('<a href="https://news.dtkt.ua/ru/simple/common/47659">ГРС против усиления контроля за использованием РРО «единщиками»</a> &#160; <i>(аналитика)</i> &#160; <b>Источник: сайт Бухгалтер 911</b>');

//        $bot = new \TelegramBot\Api\BotApi(541854266:AAHestNP3Kw89xumgUk_oS05zC7S1i5z7XI);
//        $this->bot->sendMessage(-1001195518704, '<a href="https://news.dtkt.ua/ru/simple/common/47659">ГРС против усиления контроля за использованием РРО «единщиками»</a> &#160; <i>(аналитика)</i> &#160; <b>Источник: сайт Бухгалтер 911</b>', 'HTML', true, true);

//
//            $bot->run();
//            echo "run!";

//    }

}
