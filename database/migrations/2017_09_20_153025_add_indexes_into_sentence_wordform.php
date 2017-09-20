<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesIntoSentenceWordform extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sentence_wordform', function (Blueprint $table) {
            $table->index('lemma_found');
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
            $table->dropIndex('sentence_wordform_lemma_found_index');
        });
    }
}
