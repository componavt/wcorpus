<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToTableLemmaWordform extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lemma_wordform', function (Blueprint $table) {
            $table-> foreign('lemma_id')->references('id')->on('lemmas');
            $table-> foreign('wordform_id')->references('id')->on('wordforms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lemma_wordform', function (Blueprint $table) {
            $table-> dropForeign('lemma_wordform_wordform_id_foreign');
            $table-> dropForeign('lemma_wordform_lemma_id_foreign');
        });
    }
}
