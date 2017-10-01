<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableLemmaMatrix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lemma_matrix', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lemma1')->unsigned();
            $table->integer('lemma2')->unsigned();
            
            $table->float('sim_ruscorpora',8,7)->nullable();
            $table->float('sim_news',8,7)->nullable();
            
            $table->integer('freq_12')->unsigned()->default(0);
            $table->integer('freq_21')->unsigned()->default(0);
            
            $table->unique(['lemma1', 'lemma2']);      
            $table->index('freq_12');
            $table->index('freq_21');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lemma_matrix');
    }
}
