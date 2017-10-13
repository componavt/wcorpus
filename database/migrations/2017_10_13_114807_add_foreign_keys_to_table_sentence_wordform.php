<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToTableSentenceWordform extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sentence_wordform', function (Blueprint $table) {
            $table-> foreign('sentence_id')->references('id')->on('sentences');
            $table-> foreign('wordform_id')->references('id')->on('wordforms');
            $table-> foreign('lemma_id')->references('id')->on('lemmas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sentence_wordform', function (Blueprint $table) {
            $table-> dropForeign('sentence_wordform_sentence_id_foreign');
            $table-> dropForeign('sentence_wordform_wordform_id_foreign');
            $table-> dropForeign('sentence_wordform_lemma_id_foreign');
        });
    }
}
