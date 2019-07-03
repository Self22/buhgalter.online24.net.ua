<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForeignTextsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('foreign_texts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('href', 400);
            $table->string('anchor', 5000);
            $table->string('site', 30);
            $table->longText('text');
            $table->boolean('translated')->default(0);
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
        Schema::dropIfExists('foreign_texts');
    }
}
