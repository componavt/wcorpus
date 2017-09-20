<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLemmaIdIntoSentenceWordform extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sentence_wordform', function (Blueprint $table) {
            $table->unsignedInteger('lemma_id')->nullable()->after('word_number');
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
            $table->dropColumn('lemma_id');            
        });
    }
}
