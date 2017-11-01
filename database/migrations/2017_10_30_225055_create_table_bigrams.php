<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBigrams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bigrams', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('author_id')->unsigned();
            $table->integer('text_id')->unsigned();
            $table->integer('sentence_id')->unsigned();
            
            $table->integer('lemma1')->unsigned()->nullable();
            $table->integer('lemma2')->unsigned()->nullable();
            
            $table->integer('count1')->unsigned()->nullable();
            $table->integer('count12')->unsigned()->nullable();
            
            $table->index('author_id');
            $table->index('text_id');
            $table->index('sentence_id');
            $table->index('count1');
            $table->index('count12');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bigrams');
    }
}
