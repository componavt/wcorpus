<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTableSentenceWordform extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sentence_wordform', function (Blueprint $table) {
            $table->unsignedTinyInteger('lemma_found')->nullable();
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
            $table->dropColumn('lemma_found');
        });
    }
}
