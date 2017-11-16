<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableLemmaSentenceSynset extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lemma_sentence_synset', function (Blueprint $table) {
            $table->integer('lemma_id')->unsigned();
            $table->integer('sentence_id')->unsigned();
            $table->integer('synset_id')->unsigned();
            
            $table->primary(['lemma_id','sentence_id','synset_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lemma_sentence_synset');
    }
}
