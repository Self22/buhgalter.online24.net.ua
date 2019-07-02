<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Htmldom;
use DB;


class ForeignText extends Model
{
    protected $dates = ['created_at', 'updated_at'];

    protected $fillable = ['href', 'anchor', 'site', 'text'];

    protected static function saveRawArticle($href, $anchor, $site, $text)
    {

        if (ForeignText::where('href', $href)->exists()) {
            return;
        }

        if (ForeignText::where('anchor', $anchor)->exists()) {
            return;
        }

        $article = new ForeignText;
        $article->href = $href;
        $article->anchor = trim(html_entity_decode($anchor, ENT_QUOTES));
        $article->site = $site;
        $article->text = $text;
        $article->save();
    }


    public static function parseJournalOfAccountancy(){

        $html = new \Htmldom('https://www.journalofaccountancy.com/topics/tax.html');

        $links = $html->find('h1 a');

        foreach ($links as $link){
            $anchor = $link->innertext;
            $href =  $link->href;

            $news = new \Htmldom('https://www.journalofaccountancy.com/'.$href);
            $rawTextDef = $news->find('div.default p');
            $rawTextDes = $news->find('div.inDesign p');

            $final_text = '';

            foreach ($rawTextDef as $text){
                $text = preg_replace("!<a[^>]*>(.*?)</a>!si", "\\1", $text);
                $text = preg_replace('/<img(?:\\s[^<>]*)?>/i', '', $text);
                $final_text = $final_text.$text;
            }

            foreach ($rawTextDes as $text){
                $text = preg_replace("!<a[^>]*>(.*?)</a>!si", "\\1", $text);
                $text = preg_replace('/<img(?:\\s[^<>]*)?>/i', '', $text);
                $final_text = $final_text.$text;
            }

            $href =  'https://www.journalofaccountancy.com'.$link->href;

            self::saveRawArticle($href, $anchor, 'Бухгалтерия США', $final_text);

        }
    }


}
