<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueToTableLemmaWordform extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lemma_wordform', function (Blueprint $table) {
            $table->unique(['lemma_id','wordform_id']);
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
            $table->dropUnique('lemma_wordform_lemma_id_wordform_id_unique');
        });
    }
}
