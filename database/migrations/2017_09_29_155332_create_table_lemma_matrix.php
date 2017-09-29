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
        Schema::table('lemma_matrix', function (Blueprint $table) {
            $table->integer('lemma1')->unsigned();
            $table->integer('lemma2')->unsigned();
            
            $table->primary(['lemma1', 'lemma2']);
            
            $table->float('sim_ruscorpora',8,7);
            $table->float('sim_news',8,7);
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
