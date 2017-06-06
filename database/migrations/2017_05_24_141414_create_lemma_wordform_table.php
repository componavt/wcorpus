<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLemmaWordformTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lemma_wordform', function (Blueprint $table) {
            $table->integer('lemma_id')->unsigned();
            $table->integer('wordform_id')->unsigned();
            //$table->timestamps();
            
            $table->index('lemma_id');
            $table->index('wordform_id');
            $table->index(['lemma_id','wordform_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lemma_wordform');
    }
}
