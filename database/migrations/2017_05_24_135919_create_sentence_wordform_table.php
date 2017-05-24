<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSentenceWordformTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sentence_wordform', function (Blueprint $table) {
            $table->integer('sentence_id')->unsigned();
            $table->integer('wordform_id')->unsigned();
            $table->smallInteger('word_number')->unsigned();
            //$table->timestamps();
            
            $table->primary(['sentence_id','wordform_id','word_number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sentence_wordform');
    }
}
