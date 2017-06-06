<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLangPosLemmaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lang_pos_lemma', function (Blueprint $table) {
            $table->integer('lang_pos_id')->unsigned();
            $table->integer('lemma_id')->unsigned();
            //$table->timestamps();
            
            $table->unique(['lang_pos_id','lemma_id']);
            $table->index('lang_pos_id');
            $table->index('lemma_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lang_pos_lemma');
    }
}
