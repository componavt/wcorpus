<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWordformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wordforms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('wordform',45);
            //$table->timestamps();
            
            $table->unique('wordform');
            $table->tinyInteger('lemma_total')->unsigned()->nullable();
            $table->integer('sentence_total')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wordforms');
    }
}
