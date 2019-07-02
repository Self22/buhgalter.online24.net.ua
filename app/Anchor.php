<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
//use Laravel\Scout\Searchable;
use App\Link;

class Anchor extends Model
{
//    use Searchable;

    protected $fillable = ['title', 'link_id'];


    public function link()
    {
        return $this->hasOne('App\Link');
    }
}
