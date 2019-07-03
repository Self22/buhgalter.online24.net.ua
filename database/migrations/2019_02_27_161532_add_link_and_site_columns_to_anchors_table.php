<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLinkAndSiteColumnsToAnchorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('anchors', function (Blueprint $table) {
            $table->string('href');
            $table->string('site');
            $table->string('time');
            $table->string('date');

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
            $table->dropColumn('href');
            $table->dropColumn('site');
            $table->dropColumn('time');
            $table->dropColumn('date');

        });

    }
}
