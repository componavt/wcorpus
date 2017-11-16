<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSynsetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('synsets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lemma_id')->unsigned();
            $table->text('synset')->collate('utf8_bin');
            $table->smallInteger('meaning_n')->unsigned();
            $table->text('meaning_text')->collate('utf8_bin');
            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('synsets');
    }
}
