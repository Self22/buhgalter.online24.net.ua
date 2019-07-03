<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTranslatedArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translated_articles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('href', 400);
            $table->string('anchor', 5000);
            $table->string('description', 300);
            $table->string('site', 30);
            $table->longText('text');
            $table->string('slug', 300);
            $table->integer('originId');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('translated_articles');
    }
}
