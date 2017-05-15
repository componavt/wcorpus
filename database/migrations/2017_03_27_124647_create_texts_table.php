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
            $table->integer('author_id')->unsigned()->nullable();
            $table->integer('publication_id')->unsigned()->nullable();
            $table->string('title',255);
            $table->mediumText('wikitext')->collate('utf8_bin');
            $table->mediumText('text')->nullable()->collate('utf8_bin');
            $table->smallInteger('sentence_total')->unsigned()->nullable();
            //$table->timestamps();
            
            $table->index('title');
            $table->index('text',100);
            $table->index('author_id');
            $table->index('publication_id');
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
