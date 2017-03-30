<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTextsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('texts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('page_latest')->unsigned();
            $table->string('title',255);
            $table->mediumText('wikitext')->collate('utf8_bin');
            $table->mediumText('text')->nullable()->collate('utf8_bin');
            //$table->timestamps();
            
//            $table->index('title');
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('texts');
    }
}
