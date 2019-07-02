<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Google\Cloud\Translate\TranslateClient;
use Cviebrock\EloquentSluggable\Sluggable;
use DB;
use App\ForeignText;


class TranslatedArticle extends Model
{
    use Sluggable;

    protected $dates = ['created_at', 'updated_at'];

    protected $fillable = [
        'href',
        'anchor',
        'description',
        'site',
        'text',
        'slug'];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'anchor'
            ]
        ];
    }

    protected static function saveTranslatedArticle($href, $anchor, $text, $site, $originId){

        if (TranslatedArticle::where('href', $href)->exists()) {
            return;
        }

        if (TranslatedArticle::where('anchor', $anchor)->exists()) {
            return;
        }

        $article = new TranslatedArticle();

        $article->href = $href;
        $article->anchor = htmlspecialchars_decode($anchor);

        $description = htmlspecialchars_decode(trim(stristr($text, '.', true)));
        if(mb_strlen($description) > 160){
            $description = mb_strimwidth($description, 0, 160);
            $lastEmpty = strrpos($description, ' ');
            $description = mb_strimwidth($description, 0, $lastEmpty);
        }

        $article->description = htmlspecialchars_decode($description);
        $article->text = htmlspecialchars_decode($text);
        $article->site = $site;
        $article->originId = $originId;

        $article->save();
    }

    public static function makeTranslateArticle(){

        $rawArticle = ForeignText::where('translated', 0)->firstOrFail();

        $translate = new TranslateClient([
            'key' => env('GOOGLE_CLOUD_API')
        ]);

        $anchor = $translate->translate($rawArticle->anchor, ['target' => 'ru']);
        $text = $translate->translate($rawArticle->text, ['target' => 'ru']);

        self::saveTranslatedArticle(
            $rawArticle->href,
            $anchor['text'],
            $text['text'],
            $rawArticle->site,
            $rawArticle->id);

        $rawArticle->translated = 1;
        $rawArticle->save();
    }
}
