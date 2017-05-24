<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLemmasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lemmas', function (Blueprint $table) {
            $table->increments('id');

            $table->string('lemma', 50);
            $table->boolean('dictionary');
            
            $table->tinyInteger('pos_id')->unsigned();
            $table->index('pos_id');
            //$table->foreign('pos_id')->references('id')->on('pos');
            
            $table->integer('freq')->unsigned()->nullable();
            
            $table->index('lemma');
        });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('lemmas');
    }
}
