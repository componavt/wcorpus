<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePublicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('author_id')->unsigned()->nullable();
            $table->     foreign('author_id')->references('id')->on('authors');

            $table->string('title',255);
            $table->string('creation_date',20)->nullable();
            //$table->timestamps();
            
            $table->index('title');
            $table->index('creation_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('publications');
    }
}
