<?php

namespace App;

use Htmldom;
use TelegramBot\Api\BotApi;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use naffiq\telegram\channel\Manager;

class Link extends Model

{
    public static function getDateAttribute()
    {
        setlocale(LC_ALL, 'ru' . '.utf-8', 'ru_RU' . '.utf-8', 'ru', 'ru_RU');
        return (Carbon::now('Europe/Kiev')->formatLocalized("%d %B, %Y"));
//        return '555';
    }

    public static function getTimeAttribute()
    {
        setlocale(LC_ALL, 'ru' . '.utf-8', 'ru_RU' . '.utf-8', 'ru', 'ru_RU');
        return (Carbon::now('Europe/Kiev')->format('H:i'));
//            return '555';
    }


    protected static function save_link($href, $anchor, $site, $category, $tag = 'null')
    {

        if (Link::where('href', $href)->exists()) {
            return;
        }

        $link = new Link;
        $link->href = $href;
        $link->anchor = trim(html_entity_decode($anchor));
        $link->site = $site;
        $link->category = $category;
        $link->tag = $tag;
        $link->date = Link::getDateAttribute();
        $link->time = Link::getTimeAttribute();
        $link->save();

    }

    protected static function telegram($href, $anchor, $site, $category)
    {
        if (Link::where('href', $href)->exists()) {
            return;
        }
        $anchor = trim($anchor);
        $message = '<b>' . $category . ' </b>' . '<a href="' . $href . '">' . $anchor . '</a> &#160;' . 'Источник: <b>' . $site . '</b>';

        $manager = new \naffiq\telegram\channel\Manager(env('TELEGRAM_BOT_API'), env('TELEGRAM_CHANNEL_ID'));

        $manager->postMessage($message);
    }

    public static function parse_911()
    {
        $html = new \Htmldom('https://buhgalter911.com/news/');

        $links = $html->find('.news__link');


//        // Find all links
        foreach ($links as $element) {
            $href = 'https://buhgalter911.com' . $element->href;


            /// пропускаем рекламу, выделяем рубрики, если они есть
            $anchor = strip_tags($element->innertext);
            if (strpos($anchor, 'Реклама')) {
                continue;
            } elseif (strpos($anchor, 'Важно')) {
                $anchor = str_replace('Важно', '', $anchor);
                $category = 'важно';
                if (Link::where('href', $href)->exists()) {
                    continue;
                }
                Link::telegram($href, $anchor, 'Бухгалтер911', $category);
                sleep(2);
                Link::save_link($href, $anchor, 'Бухгалтер911', $category);
            } elseif (strpos($anchor, 'Вопрос')) {

                $category = 'вопрос';
                if (Link::where('href', $href)->exists()) {
                    continue;
                }
                Link::telegram($href, $anchor, 'Бухгалтер911', $category);
                sleep(2);
                Link::save_link($href, $anchor, 'Бухгалтер911', $category);
            } elseif (strpos($anchor, 'Закон')) {

                $category = 'закон';
                if (Link::where('href', $href)->exists()) {
                    continue;
                }
                Link::telegram($href, $anchor, 'Бухгалтер911', $category);
                sleep(2);
                Link::save_link($href, $anchor, 'Бухгалтер911', $category);
            } elseif (strpos($anchor, 'Аналитика')) {
                $anchor = str_replace('Аналитика', '', $anchor);
                $category = 'аналитика';
                if (Link::where('href', $href)->exists()) {
                    continue;
                }
                Link::telegram($href, $anchor, 'Бухгалтер911', $category);
                sleep(2);
                Link::save_link($href, $anchor, 'Бухгалтер911', $category);
            } /// если рубрик нет, записываем с рубрикой "новость"
            else {
                if (Link::where('href', $href)->exists()) {
                    continue;
                }


                Link::telegram($href, $anchor, 'Бухгалтер911', 'новость');


                    sleep(2);
                    Link::save_link($href, $anchor, 'Бухгалтер911', 'новость');


            }
        }
    }

    public static function parse_buhligazakon()
    {
        $html_news = new \Htmldom('https://buh.ligazakon.net/novosti-bukhgalterii');
        $links = $html_news->find('.news__itemTitle');

//        // Find all links
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);


            $href = 'https://buh.ligazakon.net/novosti-bukhgalterii' . $element->href;
            if (Link::where('href', $href)->exists()) {
                return;
            }
            Link::telegram($href, $anchor, 'Бухгалтер.UA', 'новость');
            sleep(2);
            Link::save_link($href, $anchor, 'Бухгалтер.UA', 'новость');

        }


        $html_analytics = new \Htmldom('https://buh.ligazakon.net/bukhgalterskaya-analitika');
        $links = $html_analytics->find('.news__itemTitle');

//        // Find all links
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);


            $href = 'https://buh.ligazakon.net/bukhgalterskaya-analitika' . $element->href;
            if (Link::where('href', $href)->exists()) {
                return;
            }

            Link::telegram($href, $anchor, 'Бухгалтер.UA', 'аналитика');
            sleep(2);
            Link::save_link($href, $anchor, 'Бухгалтер.UA', 'аналитика');

        }


        $html_consultations = new \Htmldom('https://buh.ligazakon.net/konsultatsiya-po-bukhuchetu');
        $links = $html_consultations->find('.news__itemTitle');


//        // Find all links
        foreach ($links as $element) {

            $anchor = strip_tags($element->innertext);
            if (mb_strlen($anchor) > 120) {
                $key = mb_strpos($anchor, ' ', 110);
                $anchor = mb_substr($anchor, 0, $key) . '...';

            }


            $href = $element->href;
            if (Link::where('href', $href)->exists()) {
                return;
            }
            Link::telegram($href, $anchor, 'Бухгалтер.UA', 'консультация');
            sleep(2);
            Link::save_link($href, $anchor, 'Бухгалтер.UA', 'консультация');

        }

    }

    public static function parse_ifactor()
    {
        $html = new \Htmldom('https://i.factor.ua/news/');

        $links = $html->find('.b-list-in__title_link');


//        // Find all links
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);


            $href = 'https://i.factor.ua' . $element->href;
            if (Link::where('href', $href)->exists()) {
                return;
            }
            Link::telegram($href, $anchor, 'iFactor', 'новость');
            sleep(2);
            Link::save_link($href, $anchor, 'iFactor', 'новость');

        }


        $html_articles = new \Htmldom('https://i.factor.ua/articles/');

        $links = $html_articles->find('.b-free__title-name');


//        // Find all links
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);
            $category = $element->next_sibling()->innertext;


            $href = 'https://i.factor.ua' . $element->href;
            if (Link::where('href', $href)->exists()) {
                return;
            }
            Link::telegram($href, $anchor, 'iFactor', $category);
            sleep(2);
            Link::save_link($href, $anchor, 'iFactor', $category);

        }

    }

    public static function parse_dtkt()
    {
        $html = new \Htmldom('https://news.dtkt.ua/ru');

        $links = $html->find('div.content_list[rel="news_index_last"] ul li a');


//        // Find all links
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);
            $href = 'https://news.dtkt.ua' . $element->href;
            if (Link::where('href', $href)->exists()) {
                return;
            }
            Link::telegram($href, $anchor, 'Дебет-Кредит', 'новость');
            sleep(2);
            Link::save_link($href, $anchor, 'Дебет-Кредит', 'новость');

        }


        $html_consult = new \Htmldom('https://consulting.dtkt.ua/');

        $links = $html_consult->find('div.content_list[rel="consult_index_last"] ul li a');


//        // Find all links
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);
            $href = 'https://news.dtkt.ua' . $element->href;
            if (Link::where('href', $href)->exists()) {
                return;
            }



            Link::telegram($href, $anchor, 'Дебет-Кредит', 'консультация');
            sleep(2);
            Link::save_link($href, $anchor, 'Дебет-Кредит', 'консультация');


        }
    }

    public static function parse_buhgalteria()
    {

        $page_news = file_get_contents('http://www.buhgalteria.com.ua/');
        $d = mb_convert_encoding($page_news, 'HTML-ENTITIES', 'Windows-1251');

        $html = new \Htmldom($d);
        $links = $html->find('li.main-newsfeed');


//        // Find all links
        foreach ($links as $element) {
            $notags = $element->first_child();
            $first = $notags->first_child();
            $anchor = strip_tags($first->innertext);
            $anchor = iconv("ASCII", "UTF-8//IGNORE", $anchor);
            $anchor = html_entity_decode($anchor);
//            echo ($anchor);


            $href = $notags->href;
//            echo ($href) . '<br>';
            if (Link::where('href', $href)->exists()) {
                return;
            }

            Link::telegram($href, $anchor, 'Газета "Бухгалтерия"', 'новость');

                sleep(2);
                Link::save_link($href, $anchor, 'Газета "Бухгалтерия"', 'новость');


        }


    }

    public static function parse_ib()
    {
        $html = new \Htmldom('http://www.interbuh.com.ua/ru/documents/news');

        $links = $html->find('div.name a');


//        // Find all links
        foreach ($links as $element) {

            $anchor = strip_tags($element->innertext);
            $href = 'http://www.interbuh.com.ua' . $element->href;
            if (Link::where('href', $href)->exists()) {
                return;
            }
            Link::telegram($href, $anchor, 'Интерактивная бухгалтерия', 'новость');
            sleep(2);
            Link::save_link($href, $anchor, 'Интерактивная бухгалтерия', 'новость');

        }
    }
}
