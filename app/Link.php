<?php

namespace App;

use Htmldom;
use Cviebrock\EloquentSluggable\Sluggable;
use TelegramBot\Api\BotApi;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use naffiq\telegram\channel\Manager;
use JonnyW\PhantomJs\Client;



class Link extends Model
{
    use Sluggable;

    protected $fillable = ['href', 'anchor', 'site', 'category', 'tag', 'time', 'date', 'news_text', 'slug', 'banner_link', 'description', 'tel_pub'];

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
//        return '555';
    }

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'anchor'
            ]
        ];
    }



    protected static function save_link($href, $anchor, $site, $category, $news_text, $tag = 'null', $banner_link = 'null')
    {

        if (Link::where('href', $href)->exists()) {
            return;
        }

        if (Link::where('anchor', $anchor)->exists()) {
            return;
        }

        $link = new Link;
        $link->href = $href;
        $link->anchor = trim(html_entity_decode($anchor));
        $link->site = $site;
        $link->category = $category;
        $link->tag = $tag;
        $link->banner_link = $banner_link;
        $link->news_text = $news_text;
        $link->description = htmlspecialchars_decode(strip_tags(stristr($news_text, '.', true)));
        $link->date = Link::getDateAttribute();
        $link->time = Link::getTimeAttribute();
        $link->tel_pub = 'not_pub';
        $link->save();

    }

    protected static function telegram()
    {
        $links = Link::where('tel_pub', 'not_pub')->get();;

        foreach ($links as $link) {
            $message = '<b>' . $link->category . ' </b>' . '<a href="https://buhgalter.online24.net.ua/buhgalterskaya_novost/' . $link->slug . '">' . $link->anchor . '</a> &#160;' . 'Источник: <b>' . $link->site . '</b>';
            $manager = new \naffiq\telegram\channel\Manager(env('TELEGRAM_BOT_API'), env('TELEGRAM_CHANNEL_ID'));
            $manager->postMessage($message);
            $link->tel_pub = 'pub';
            $link->save();

        }


    }

    public static function parse_911()
    {
        $html = new \Htmldom('https://buhgalter911.com/news/');

        $links = $html->find('.news__link');


//        // Find all links
        foreach ($links as $element) {
            $href = 'https://buhgalter911.com' . $element->href;
            $category = 'новость';

            /// пропускаем рекламу, выделяем рубрики, если они есть
            $anchor = strip_tags($element->innertext);
            if (strpos($anchor, 'Реклама')) {
                continue;
            } elseif (strpos($anchor, 'интернет-изданий')) {
                continue;
            } elseif
            (strpos($anchor, 'Важно')) {
                $anchor = str_replace('Важно', '', $anchor);
                $category = 'важно';

            } elseif (strpos($anchor, 'Вопрос')) {

                $category = 'вопрос';

            } elseif (strpos($anchor, 'Закон')) {

                $category = 'закон';

            } elseif (strpos($anchor, 'Аналитика')) {
                $anchor = str_replace('Аналитика', '', $anchor);
                $category = 'аналитика';

            } /// если рубрик нет, записываем с рубрикой "новость"
            else {
                if (Link::where('href', $href)->exists()) {
                    continue;
                }

                $raw_text = new \Htmldom($href);
                $e = $raw_text->find('.c_content p');
                $final_text = '';


                foreach ($e as $live) {
                    $live = preg_replace("!<a[^>]*>(.*?)</a>!si", "\\1", $live);
                    $live = str_replace('align="justify"', '', $live);
                    $live = preg_replace('/<img(?:\\s[^<>]*)?>/i', '', $live);
                    $live = preg_replace("'<font[^>]*?>.*?</font>'si", "", $live);
                    /*   $live = preg_replace("'<b[^>]*?>.*?</b>'si","",$live);*/

                    $final_text = $final_text . $live;

                }


                Link::save_link($href, $anchor, 'Бухгалтер911', $category, $final_text);



            }
        }
    }

    public static function parse_buhligazakon_news()
    {
        $html_news = new \Htmldom('https://buh.ligazakon.net/novosti-bukhgalterii');
        $links = $html_news->find('.news__itemTitle');

        // Зайти в цикле в кадый линк, вытащить, очистить и сохранить текст новости
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);
            $href = 'https://buh.ligazakon.net' . $element->href;
            if (Link::where('href', $href)->exists()) {
                return;
            }

            $raw_text = new \Htmldom($href);
            $e = $raw_text->find('.news__fullText p');
            $final_text = '';


            foreach ($e as $live) {
                $live = preg_replace("!<a[^>]*>(.*?)</a>!si", "\\1", $live);
                $live = str_replace('align="justify"', '', $live);
                $live = preg_replace('/<img(?:\\s[^<>]*)?>/i', '', $live);
                $live = preg_replace("'<font[^>]*?>.*?</font>'si", "", $live);
                /*   $live = preg_replace("'<b[^>]*?>.*?</b>'si","",$live);*/

                $final_text = $final_text . $live;

            }


            Link::save_link($href, $anchor, 'Бухгалтер.UA', 'новость', $final_text);


        }

    }

    public static function parse_buhligazakon_analytics()
    {
        $html_analytics = new \Htmldom('https://buh.ligazakon.net/bukhgalterskaya-analitika');
        $links = $html_analytics->find('.news__itemTitle');

        // Зайти в цикле в кадый линк, вытащить, очистить и сохранить текст новости
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);
            $href = 'https://buh.ligazakon.net' . $element->href;

            if (Link::where('href', $href)->exists()) {
                return;
            }

            $raw_text = new \Htmldom($href);
            $e = $raw_text->find('.news__fullText p');
            $final_text = '';


            foreach ($e as $live) {
                $live = preg_replace("!<a[^>]*>(.*?)</a>!si", "\\1", $live);
                $live = str_replace('align="justify"', '', $live);
                $live = preg_replace('/<img(?:\\s[^<>]*)?>/i', '', $live);
                $live = preg_replace("'<font[^>]*?>.*?</font>'si", "", $live);
                /*   $live = preg_replace("'<b[^>]*?>.*?</b>'si","",$live);*/

                $final_text = $final_text . $live;

            }

            Link::save_link($href, $anchor, 'Бухгалтер.UA', 'аналитика', $final_text);

        }

    }


    public static function parse_buhligazakon_consultations()
    {
        $html_consultations = new \Htmldom('https://buh.ligazakon.net/konsultatsiya-po-bukhuchetu');
        $links = $html_consultations->find('.news__itemTitle');


//        // Find all links
        foreach ($links as $element) {
            $href = $element->href;

            if (Link::where('href', $href)->exists()) {
                return;
            }
            $anchor = strip_tags($element->innertext);
            // если анкор слишком длинный обрезаем
            if (mb_strlen($anchor) > 120) {
                $key = mb_strpos($anchor, ' ', 110);
                $anchor = mb_substr($anchor, 0, $key) . '...';

            }

            $raw_text = new \Htmldom($href);
            $e = $raw_text->find('.mainTag');
            $final_text = '';


            foreach ($e as $live) {
                $live = preg_replace("!<a[^>]*>(.*?)</a>!si", "\\1", $live);


                /*   $live = preg_replace("'<b[^>]*
?>.*?</b>'si","",$live);*/

                $final_text = $final_text . $live;

            }


//            Link::telegram($href, $anchor, 'Бухгалтер.UA', 'консультация');
//            sleep(5);
            Link::save_link($href, $anchor, 'Бухгалтер.UA', 'консультация', $final_text);

        }
    }


    public static function parse_ifactor_news()
    {
        $html = new \Htmldom('https://i.factor.ua/news/');

        $links = $html->find('.b-list-in__title_link');

// Зайти в цикле в кадый линк, вытащить, очистить и сохранить текст новости
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);
            if (strpos($anchor, 'интернет-изданий')) {
                continue;
            }
            $href = 'https://i.factor.ua' . $element->href;

            if (Link::where('href', $href)->exists()) {
                return;
            }

            $raw_text = new \Htmldom($href);
            $e = $raw_text->find('.b-item__content');
            $final_text = '';


            foreach ($e as $live) {
                $live = preg_replace("!<a[^>]*>(.*?)</a>!si", "\\1", $live);
                $live = str_replace('align="justify"', '', $live);
                $live = preg_replace('/<img(?:\\s[^<>]*)?>/i', '', $live);
                $live = preg_replace("'<font[^>]*?>.*?</font>'si", "", $live);
                /*   $live = preg_replace("'<b[^>]*?>.*?</b>'si","",$live);*/

                $final_text = $final_text . $live;

            }


            Link::save_link($href, $anchor, 'iFactor', 'новость', $final_text);

        }


    }

    public static function parse_ifactor_articles()
    {

        $html_articles = new \Htmldom('https://i.factor.ua/articles/');

        $links = $html_articles->find('.b-free__title-name');


//        // Find all links
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);
            $category = $element->next_sibling()->innertext;
            $href = 'https://i.factor.ua' . $element->href;

            $client = Client::getInstance();
            $client->getEngine()->setPath('/home/seryalon/online24.net.ua/buhgalter/bin/phantomjs');        /**
             * @see JonnyW\PhantomJs\Http\Request         **/
            $request = $client->getMessageFactory()->createRequest($href, 'GET');
            /**
             * @see JonnyW\PhantomJs\Http\Response
             **/
            $response = $client->getMessageFactory()->createResponse();
            // Send the request
            $client->send($request, $response);

            if($response->getStatus() === 200) {
                $html_article = new \Htmldom($response->getContent());
                $text = $html_article->find('p.indent');
                $final_text = '';

//        // Find and clean all paragraphs
                foreach ($text as $e) {

                    $e->align = null;
                    $e->class = null;
                    $e->style = null;
                    $e = preg_replace("!<a[^>]*>(.*?)</a>!si", "\\1", $e);
                    $e = preg_replace('/<img(?:\\s[^<>]*)?>/i', '', $e);
                    $e = preg_replace("'<font[^>]*?>.*?</font>'si", "", $e);
                    $e = preg_replace("'<span[^>]*?>.*?</span>'si", "", $e);
                    $final_text = $final_text . $e;
                }

                Link::save_link($href, $anchor, 'iFactor', $category, $final_text);

            }

        }

    }

    public static function parse_dtkt_news()
    {
        $html = new \Htmldom('https://news.dtkt.ua/ru');

        $links = $html->find('div.content_list[rel="news_index_last"] ul li a');

// Зайти в цикле в кадый линк, вытащить, очистить и сохранить текст новости
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);
            if (strpos($anchor, 'интернет-изданий')) {
                continue;
            }
            $href = 'https://news.dtkt.ua' . $element->href;

            if (Link::where('href', $href)->exists()) {
                return;
            }

            $raw_text = new \Htmldom($href);
            $e = $raw_text->find('.fulltext p');
            $final_text = '';


            foreach ($e as $live) {
                if (strpos($live, 'читайте тут')) {
                    continue;
                }
                $live->style = null;
                $live = preg_replace("!<a[^>]*>(.*?)</a>!si", "\\1", $live);
                $live = preg_replace('/<img(?:\\s[^<>]*)?>/i', '', $live);
                $live = preg_replace("'<font[^>]*?>.*?</font>'si", "", $live);
                /*   $live = preg_replace("'<b[^>]*?>.*?</b>'si","",$live);*/
                $live = str_replace('Читайте також:', '', $live);
                $live = str_replace('Усі новини на тему', '', $live);
                $live = str_replace('&gt;', '', $live);
                $final_text = $final_text . $live;


            }


            Link::save_link($href, $anchor, 'Дебет-Кредит', 'новость', $final_text);

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


            $href = $notags->href;

            if (Link::where('href', $href)->exists()) {
                return;
            }

            $raw_text = new \Htmldom($href);
            $e = $raw_text->find('.columns');
            if (empty($e)) {
                $e = $raw_text->find('.text p');
            }


            $final_text = '';


            foreach ($e as $live) {
                $live->align = null;
                $live->class = null;
                $live->style = null;
                $live = preg_replace("!<a[^>]*>(.*?)</a>!si", "\\1", $live);
                $live = preg_replace('/<img(?:\\s[^<>]*)?>/i', '', $live);
                $live = preg_replace("'<font[^>]*?>.*?</font>'si", "", $live);
                /*   $live = preg_replace("'<b[^>]*?>.*?</b>'si","",$live);*/
                $live = str_replace('Читайте також:', '', $live);
                $live = str_replace('Усі новини на тему', '', $live);
                $live = str_replace('&gt;', '', $live);
                $final_text = $final_text . $live;


            }

            if (empty($final_text)) {
                continue;
            }


            Link::save_link($href, $anchor, 'Газета "Бухгалтерия"', 'новость', $final_text);


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

            $raw_text = new \Htmldom($href);
            $e = $raw_text->find('.news-content p');
            $final_text = '';


            foreach ($e as $live) {
                if (strpos($live, 'читайте тут')) {
                    continue;
                }
                $live->align = null;
                $live->class = null;
                $live->style = null;
                $live = preg_replace("!<a[^>]*>(.*?)</a>!si", "\\1", $live);
                $live = preg_replace('/<img(?:\\s[^<>]*)?>/i', '', $live);
                $live = preg_replace("'<font[^>]*?>.*?</font>'si", "", $live);
                /*   $live = preg_replace("'<b[^>]*?>.*?</b>'si","",$live);*/

                $final_text = $final_text . $live;


            }


//
//
            Link::save_link($href, $anchor, 'Интерактивная бухгалтерия', 'новость', $final_text);


        }
    }

    public static function parse_balance()
    {
        $html = new \Htmldom('https://balance.ua/uteka/');

        $links = $html->find('h2 a');
        foreach ($links as $element) {

            $anchor = strip_tags($element->innertext);
            $href = $element->href;


            if (Link::where('href', $href)->exists()) {
                return;
            }

            $raw_text = new \Htmldom($href);
            $e = $raw_text->find('.js-mediator-article p');
            $final_text = '';


            foreach ($e as $live) {

                $live->align = null;
                $live->class = null;
                $live->style = null;
                $live = preg_replace("!<a[^>]*>(.*?)</a>!si", "\\1", $live);
                $live = preg_replace('/<img(?:\\s[^<>]*)?>/i', '', $live);
                $live = preg_replace("'<font[^>]*?>.*?</font>'si", "", $live);
                $live = preg_replace("'<span[^>]*?>.*?</span>'si", "", $live);
                /*   $live = preg_replace("'<b[^>]*?>.*?</b>'si","",$live);*/

                $final_text = $final_text . $live;


            }


            Link::save_link($href, $anchor, 'Баланс', 'новость', $final_text);

        }
    }

    public static function test_phantom()
    {

        $html_articles = new \Htmldom('https://i.factor.ua/articles/');

        $links = $html_articles->find('.b-free__title-name');


//        // Find all links
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);
            $category = $element->next_sibling()->innertext;
            $href = 'https://i.factor.ua' . $element->href;



            echo ($href).($category).($anchor);


        }

    }
}
