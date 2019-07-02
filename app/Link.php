<?php

namespace App;

use Htmldom;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ParseEmail;
use Cviebrock\EloquentSluggable\Sluggable;
use TelegramBot\Api\BotApi;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use naffiq\telegram\channel\Manager;
use JonnyW\PhantomJs\Client;
use Telegram\Bot\Api;
use Google\Cloud\Translate\TranslateClient;


class Link extends Model
{
    use Sluggable;

    protected $dates = ['created_at', 'updated_at'];

    protected $fillable = ['href', 'anchor', 'site', 'category', 'tag', 'time', 'date', 'news_text', 'slug', 'banner_link', 'description', 'tel_pub'];

    public function anchor()
    {
        return $this->belongsTo('App\Anchor');
    }

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
        $link->anchor = trim(html_entity_decode($anchor, ENT_QUOTES));
        $link->site = $site;
        $link->category = $category;
        $link->tag = $tag;
        $link->banner_link = $banner_link;
        $link->news_text = $news_text;

        $description = htmlspecialchars_decode(trim(stristr($news_text, '.', true)));
        if(mb_strlen($description) > 160){
            $description = mb_strimwidth($description, 0, 160);
            $lastEmpty = strrpos($description, ' ');
            $description = mb_strimwidth($description, 0, $lastEmpty);
        }
        $link->description = $description;

        $link->date = Link::getDateAttribute();
        $link->time = Link::getTimeAttribute();
        $link->tel_pub = 'not_pub';
        $link->save();


        // the code below saved Anchor object for Algolia Search

//        $title = new Anchor();
//        $title->title = trim(html_entity_decode($anchor, ENT_QUOTES));
//        $title->link_id = $link->id;
//        $title->href = $link->href;
//        $title->site = $link->site;
//        $title->time = $link->time;
//        $title->date = $link->date;
//        $title->category = $category;
//        $title->link_id = $link->id;
//        $title->save();

    }

    public static function telegram()
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
        $html_news = new \Htmldom('https://buh.ligazakon.net/news/');
        $links = $html_news->find('.article_title');

        // Зайти в цикле в кадый линк, вытащить, очистить и сохранить текст новости
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);
            $href = 'https://buh.ligazakon.net' . $element->href;
            if (Link::where('href', $href)->exists()) {
                continue;
            }

            $raw_text = new \Htmldom($href);
            $e = $raw_text->find('p[align]');
            $final_text = '';


            foreach ($e as $live) {
                $live = preg_replace("!<a[^>]*>(.*?)</a>!si", "\\1", $live);
                $live = str_replace('align="justify"', '', $live);
                $live = str_replace('align="center"', '', $live);
                $live = str_replace('align="left"', '', $live);
                $live = preg_replace('/<img(?:\\s[^<>]*)?>/i', '', $live);
                $live = str_replace("по ссылке", '<a href="http://online24.net.ua/products/ligazakon.html">в системах ЛІГА:ЗАКОН</a>', $live);
                $live = str_replace("воспользуйтесь ТЕСТОВЫМ доступом к сервису", '<a href="http://online24.net.ua/products/ligazakon.html">воспользуйтесь ТЕСТОВЫМ доступом к сервису</a>', $live);
                $live = str_replace("Детали акции", '<a href="http://online24.net.ua/products/ligazakon.html">Закажите на Online24</a>', $live);
                $live = str_replace("БУХГАЛТЕР&amp;ЗАКОН", '<a href="http://online24.net.ua/products/ligazakon.html">БУХГАЛТЕР&ЗАКОН на Online24</a>', $live);
                $live = str_replace("ТЕСТОВЫМ доступом к сервису", '<a href="http://online24.net.ua/products/ligazakon.html">ТЕСТОВЫМ доступом к сервису</a>', $live);
                $live = str_replace("скористайтеся ТЕСТОВИМ 3-денним доступом до сервісу", '<a href="http://online24.net.ua/products/interbuh.html">замовте підписку на сервіс на сайті Online24</a>', $live);
                $live = str_replace("воспользуйтесь ТЕСТОВЫМ 3-дневным доступом к сервису", '<a href="http://online24.net.ua/products/interbuh.html">закажите подписку на сервис на сайте Online24</a>', $live);
                $live = preg_replace("'<font color=\"#e12000\"[^>]*?>.*?</font>'si", "", $live);
                /*   $live = preg_replace("'<b[^>]*?>.*?</b>'si","",$live);*/

                $final_text = $final_text . $live;

            }


            Link::save_link($href, $anchor, 'Бухгалтер.UA', 'новость', $final_text);


        }

    }

    public static function parse_buhligazakon_analytics()
    {
        $html_analytics = new \Htmldom('https://buh.ligazakon.net/ua/analitycs');
        $links = $html_analytics->find('.article_title');

        // Зайти в цикле в кадый линк, вытащить, очистить и сохранить текст новости
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);
            $href = 'https://buh.ligazakon.net' . $element->href;

            if($href == 'https://buh.ligazakon.net'){
                continue;
            }
            if (Link::where('href', $href)->exists()) {
                continue;
            }


            $raw_text = new \Htmldom($href);
            $e = $raw_text->find('p[align]');
            $final_text = '';


            foreach ($e as $live) {
                $live = preg_replace("!<a[^>]*>(.*?)</a>!si", "\\1", $live);
                $live = str_replace('align="justify"', '', $live);
                $live = str_replace('align="center"', '', $live);
                $live = str_replace('align="left"', '', $live);
                $live = preg_replace('/<img(?:\\s[^<>]*)?>/i', '', $live);
                $live = str_replace("за посиланням", '<a href="http://online24.net.ua/products/ligazakon.html">в системах ЛІГА:ЗАКОН</a>', $live);
                $live = str_replace("скористайтеся ТЕСТОВИМ доступом до сервісу", '<a href="http://online24.net.ua/products/ligazakon.html">скористайтеся ТЕСТОВИМ доступом до сервісу</a>', $live);
                $live = str_replace("Деталі акції", '<a href="http://online24.net.ua/products/ligazakon.html">Замовте зараз на Online24</a>', $live);
                $live = preg_replace("'<font color=\"#e12000\"[^>]*?>.*?</font>'si", "", $live);
                /*   $live = preg_replace("'<b[^>]*?>.*?</b>'si","",$live);*/

                $final_text = $final_text . $live;

            }

            Link::save_link($href, $anchor, 'Бухгалтер.UA', 'аналитика', $final_text);

        }

    }


//    public static function parse_buhligazakon_consultations()
//    {
//        $html_consultations = new \Htmldom('https://buh.ligazakon.net/konsultatsiya-po-bukhuchetu');
//        $links = $html_consultations->find('.news__itemTitle');
//
//
////        // Find all links
//        foreach ($links as $element) {
//            $href = $element->href;
//
//            if (Link::where('href', $href)->exists()) {
//                return;
//            }
//            $anchor = strip_tags($element->innertext);
//            // если анкор слишком длинный обрезаем
//            if (mb_strlen($anchor) > 120) {
//                $key = mb_strpos($anchor, ' ', 110);
//                $anchor = mb_substr($anchor, 0, $key) . '...';
//
//            }
//
//            $raw_text = new \Htmldom($href);
//            $e = $raw_text->find('.mainTag');
//            $final_text = '';
//
//
//            foreach ($e as $live) {
//                $live = preg_replace("!<a[^>]*>(.*?)</a>!si", "\\1", $live);
//
//
//                /*   $live = preg_replace("'<b[^>]*
/*?>.*?</b>'si","",$live);*/
//
//                $final_text = $final_text . $live;
//
//            }
//
//
////            Link::telegram($href, $anchor, 'Бухгалтер.UA', 'консультация');
////            sleep(5);
//            Link::save_link($href, $anchor, 'Бухгалтер.UA', 'консультация', $final_text);
//
//        }
//    }


    public static function parse_ifactor_news()
    {
        $html = new \Htmldom('https://i.factor.ua/news/');

        $links = $html->find('.b-list-in__title_link');

// Зайти в цикле в кадый линк, вытащить, очистить и сохранить текст новости
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);

            if(strpos($anchor,  'Cеминар')){
                continue;
            }

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
                $live->style = null;
                $live = preg_replace('/style=\\"[^\\"]*\\"/', "", $live);
                $live = preg_replace("!<a[^>]*>(.*?)</a>!si", "", $live);
                $live = str_replace('align="justify"', '', $live);
                $live = str_replace('font-family:', '', $live);
                $live = preg_replace('/<img(?:\\s[^<>]*)?>/i', '', $live);
                $live = preg_replace("'<font[^>]*?>.*?</font>'si", "", $live);
                $live = preg_replace("'<iframe[^>]*?>.*?</iframe>'si", "", $live);
                /*   $live = preg_replace("'<b[^>]*?>.*?</b>'si","",$live);*/

                $final_text = $final_text . $live;

            }


            Link::save_link($href, $anchor, 'iFactor', 'новость', $final_text);

        }


    }

//    public static function parse_ifactor_articles()
//    {
//
//        $html_articles = new \Htmldom('https://i.factor.ua/articles/');
//
//        $links = $html_articles->find('.b-free__title-name');
//
//
////        // Find all links
//        foreach ($links as $element) {
//            $anchor = strip_tags($element->innertext);
//            $category = $element->next_sibling()->innertext;
//            $href = 'https://i.factor.ua' . $element->href;
//
//            $client = Client::getInstance();
//            $client->getEngine()->setPath('/home/seryalon/online24.net.ua/buhgalter/bin/phantomjs');        /**
//             * @see JonnyW\PhantomJs\Http\Request         **/
//            $request = $client->getMessageFactory()->createRequest($href, 'GET');
//            /**
//             * @see JonnyW\PhantomJs\Http\Response
//             **/
//            $response = $client->getMessageFactory()->createResponse();
//            // Send the request
//            $client->send($request, $response);
//
//            if($response->getStatus() === 200) {
//                $html_article = new \Htmldom($response->getContent());
//                $text = $html_article->find('p.indent');
//                $final_text = '';
//
////        // Find and clean all paragraphs
//                foreach ($text as $e) {
//
//                    $e->align = null;
//                    $e->class = null;
//                    $e->style = null;
//                    $e = preg_replace("!<a[^>]*>(.*?)</a>!si", "\\1", $e);
/*                    $e = preg_replace('/<img(?:\\s[^<>]*)?>/i', '', $e);*/
/*                    $e = preg_replace("'<font[^>]*?>.*?</font>'si", "", $e);*/
/*                    $e = preg_replace("'<span[^>]*?>.*?</span>'si", "", $e);*/
//                    $final_text = $final_text . $e;
//                }
//
//                Link::save_link($href, $anchor, 'iFactor', $category, $final_text);
//
//            }
//
//        }
//
//    }

    public static function parse_dtkt_news()
    {
        $html = new \Htmldom('https://news.dtkt.ua/ru');

        $links = $html->find('.article-info__title');

// Зайти в цикле в кадый линк, вытащить, очистить и сохранить текст новости
        foreach ($links as $element) {
            $anchor = strip_tags($element->innertext);
            if (strpos($anchor, 'интернет-изданий')) {
                continue;
            }
            $href = $element->href;

//            echo ($href).($anchor);

            if (Link::where('href', $href)->exists()) {
                continue;
            }



            $raw_text = new \Htmldom($href);
            $e = $raw_text->find('.fulltext');

            $final_text = '';


            foreach ($e as $live) {
                if (strpos($live, 'читайте тут')) {
                    continue;
                }
                $live->style = null;
                $live = preg_replace("!<a[^>]*>(.*?)</a>!si", "\\1", $live);
                $live = preg_replace('/<img(?:\\s[^<>]*)?>/i', '', $live);
                $live = preg_replace("'<font[^>]*?>.*?</font>'si", "", $live);
                $live = preg_replace("'<b[^>]*?>.*?</b>'si","",$live);
                $live = preg_replace("'<strong[^>]*?>.*?</strong>'si","",$live);
                $live = str_replace('Читайте також:', '', $live);
                $live = str_replace('Усі новини на тему', '', $live);
                $live = str_replace('&gt;', '', $live);
                $final_text = $final_text . $live;


            }
//            echo $href.'<br>'.$anchor.'<br>'.(strip_tags($final_text)).'<br><br>';

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
                continue;
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
                continue;
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
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );

        $response = file_get_contents("https://balance.ua/uteka/", false, stream_context_create($arrContextOptions));

        $html = new \Htmldom($response);

        $links = $html->find('h2 a');
        foreach ($links as $element) {

            $anchor = strip_tags($element->innertext);
            $href = $element->href;


            if (Link::where('href', $href)->exists()) {
                continue;
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

    public static function testTelegramIncoming(){

        $telegram = new Api('685659754:AAEZjxFp4_xSBGBN0_rbGYARmIF4wAA0j8U');

        $response = $telegram->getMe();

        $botId = $response->getId();
        $firstName = $response->getFirstName();
        $username = $response->getUsername();
        echo ($botId).'<br>'.($firstName).'<br>'.($username).'<br>';
        echo '<pre>'.(print_r($response)).'</pre>';

        $newResponse = $telegram->getUpdates();
//        echo "<pre>";
//        print_r($newResponse);
//        echo "</pre>";

        foreach ($newResponse as $item => $value){
            echo $value['channel_post']['message_id'].'<br>';
            echo '<hr>';
            echo $item.'<br>'.$value;
        }

    }

    public static function testMail(){
        $content = 'test';
        Mail::to(config('mail.support.address'))->send(new ParseEmail($content));
        echo('hello');
    }

    public static function testParseResults(){

        $now = Carbon::now();
        $message = '';

        /// проверка парсера журнала iFactor
        $idIfactor = DB::table('links')
            ->where('site', 'iFactor')
            ->max('id');
        $articleIfactor = Link::where('id', $idIfactor)->first();


        if(($now->diffInDays($articleIfactor->created_at)) > 5){
            $message = $message.'<p>Парсер журнала iFactor не работает больше 5 дней</p>';
        }

        /// проверка парсера журнала 911
        $id911 = DB::table('links')
            ->where('site', 'Бухгалтер911')
            ->max('id');
        $article911 = Link::where('id', $id911)->first();


        if(($now->diffInDays($article911->created_at)) > 5){
            $message = $message.'<p>Парсер журнала Бухгалтер не работает больше 5 дней</p>';
        }

        /// проверка парсера журнала Баланс
        $idBalance = DB::table('links')
            ->where('site', 'Баланс')
            ->max('id');
        $articleBalance = Link::where('id', $idBalance)->first();


        if(($now->diffInDays($articleBalance->created_at)) > 5){
            $message = $message.'<p>Парсер журнала Баланс не работает больше 5 дней</p>';
        }

        /// проверка парсера журнала Бухгалтер.UA - аналитика
        $idBuhAnal = DB::table('links')
            ->where('site', 'Бухгалтер.UA')
            ->where('category', 'аналитика')
            ->max('id');
        $articleBuhAnal = Link::where('id', $idBuhAnal)->first();


        if(($now->diffInDays($articleBuhAnal->created_at)) > 5){
            $message = $message.'<p>Парсер журнала Бухгалтер.UA - аналитика не работает больше 5 дней</p>';
        }

        /// проверка парсера журнала Бухгалтер.UA - новости
        $idBuhNews = DB::table('links')
            ->where('site', 'Бухгалтер.UA')
            ->where('category', 'новость')
            ->max('id');
        $articleBuhNews = Link::where('id', $idBuhNews)->first();


        if(($now->diffInDays($articleBuhNews->created_at)) > 5){
            $message = $message.'<p>Парсер журнала Бухгалтер.UA - новости не работает больше 5 дней</p>';
        }


        /// проверка парсера журнала Дебет-Кредит
        $idDK = DB::table('links')
            ->where('site', 'Дебет-Кредит')
            ->max('id');
        $articleDK = Link::where('id', $idDK)->first();


        if(($now->diffInDays($articleDK->created_at)) > 5){
            $message = $message.'<p>Парсер журнала Дебет-Кредит - аналитика не работает больше 5 дней</p>';
        }

        /// отправка сообщения с ошибками на ящик

        if(strlen($message) > 5){
            Mail::to(config('mail.support.address'))->send(new ParseEmail($message));
        }

    }

    public static function exportSql(){
        $articles = Anchor::all();

        $articlesJson = $articles->toJson();
        file_put_contents('sql.json', $articlesJson);
//        echo'<pre>';
//        echo($articles->toJson());
//        echo'</pre>';

    }

    public static function copyAnchors(){
        DB::table('links')->where('ancored', 0)
            ->chunkById(100, function ($articles) {
                foreach ($articles as $article) {

                    $anchor = new Anchor();
                    $anchor->title = $article->anchor;
                    $anchor->href = $article->slug;
                    $anchor->title = $article->anchor;
                    $anchor->site = $article->site;
                    $anchor->time = $article->time;
                    $anchor->date = $article->date;
                    $anchor->category = $article->category;
                    $anchor->link_id = $article->id;
                    $anchor->save();


                    DB::table('links')
                        ->where('id', $article->id)
                        ->update(['ancored' => 1]);
                }
            });

//                DB::table('links')->where('ancored', 1)
//            ->chunkById(50, function ($articles) {
//                foreach ($articles as $article) {
//
//
//                    DB::table('links')
//                        ->where('id', $article->id)
//                        ->update(['ancored' => 0]);
//                }
//            });
    }

    public static function testGoogleTranslate(){
        $translate = new TranslateClient([
            'key' => 'AIzaSyDKthxNLsVz8THve60XpFjtjX96GgH8FOE'
        ]);

// Translate text from english to french.
        $result = $translate->translate('<p>A transfer-pricing regulation that requires related entities to share the cost of employee stock compensation is a valid regulation, the Ninth Circuit held on Friday (<i>Altera Corp.</i>, No. 16-70496 (9th Cir. 6/7/19)). The appeals court reversed a Tax Court decision that had held Regs. Sec. 1.482-7A(d)(2) was invalid under the Administrative Procedure Act (APA) (<i>Altera</i>, 145 T.C. 91 (2015)). The Ninth Circuit originally decided this case in July 2018 but withdrew its opinion because one of the judges involved, Judge Stephen Reinhardt, had died before the opinion was issued. Reinhardt was replaced on the three-judge panel by Judge Susan Graber.</p>', [
            'target' => 'uk'
        ]);

        echo $result['text'] . "\n";
    }
}
