<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKeysToTableBigramAuthor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bigram_author', function (Blueprint $table) {
            $table->increments('id')->first();
            $table->unique(['author_id','lemma1','lemma2']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bigram_author', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->dropUnique('bigram_author_author_id_lemma1_lemma2_unique');
        });
    }
}
