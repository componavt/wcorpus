<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBigramAuthorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bigram_author', function (Blueprint $table) {
            $table->integer('author_id')->unsigned();
            
            $table->integer('lemma1')->unsigned()->nullable();
            $table->integer('lemma2')->unsigned()->nullable();
            
            $table->integer('count1')->unsigned()->default(0);
            $table->integer('count12')->unsigned()->default(0);
            
            $table->index('author_id');
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
        Schema::dropIfExists('lemma_matrix');
    }
}
