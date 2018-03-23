<?php

namespace App;

use Htmldom;
use TelegramBot\Api\BotApi;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Link extends Model

{
    public static function getDateTimeAttribute()
    {
        setlocale(LC_ALL, 'ru' . '.utf-8', 'ru_RU' . '.utf-8', 'ru', 'ru_RU');

        return iconv('cp1251', 'utf-8', (Carbon::now('Europe/Kiev')->formatLocalized("%d %B, %Y")) . ' ' . (Carbon::now('Europe/Kiev')->format('H:i')));
    }


    public static function save_link($href, $anchor, $site, $category, $tag = 'null'){

        if (Link::where('href', $href)->exists()) {
            return;
        }

        $link = new Link;
        $link->href = $href;
        $link->anchor = trim($anchor);
        $link->site = $site;
        $link->category = $category;
        $link->tag = $tag;
        $link->time =  Link::getDateTimeAttribute();
        $link->save();

    }

    public static function parse_911()
    {
        $html = new \Htmldom('https://buhgalter911.com/news/');

        $links = $html->find('.news__link  ');


//        // Find all links
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);
            if (strpos($anchor, 'Реклама')) {
                continue;
            }
            $anchor = str_replace('Важно', '', $anchor);
            $anchor = str_replace('Вопрос', 'Вопрос:', $anchor);


            $href = 'https://buhgalter911.com' . $element->href;
            Link::save_link($href, $anchor, 'Бухгалтер911', 'новость');


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
            Link::save_link($href, $anchor, 'Бухгалтер.UA', 'новость');

        }


        $html_analytics = new \Htmldom('https://buh.ligazakon.net/bukhgalterskaya-analitika');
        $links = $html_analytics->find('.news__itemTitle');

//        // Find all links
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);


            $href = 'https://buh.ligazakon.net/bukhgalterskaya-analitika' . $element->href;
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



            $href = 'https://i.factor.ua'.$element->href;

            Link::save_link($href, $anchor, 'iFactor', 'новость');

        }


        $html_articles = new \Htmldom('https://i.factor.ua/articles/');

        $links = $html_articles->find('.b-free__title-name');


//        // Find all links
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);
            $category = $element->next_sibling()->innertext;


            $href = 'https://i.factor.ua'.$element->href;
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
            Link::save_link($href, $anchor, 'Дебет-Кредит', 'новость');

        }


        $html_consult = new \Htmldom('https://consulting.dtkt.ua/');

        $links = $html_consult->find('div.content_list[rel="consult_index_last"] ul li a');


//        // Find all links
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);
            $href = 'https://news.dtkt.ua' . $element->href;
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
            Link::save_link($href, $anchor, 'Интерактивная бухгалтерия', 'новость');

        }
    }
}