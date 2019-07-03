<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategoryColumnToAnchorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('anchors', function (Blueprint $table) {
            $table->string('category');
            $table->longText('href')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('anchors', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->string('href')->change();
        });
    }
}
