<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKeysToTableBigrams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bigrams', function (Blueprint $table) {
            $table->index(['author_id','lemma1']);
            $table->index(['author_id','lemma1','lemma2']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bigrams', function (Blueprint $table) {
            $table->dropIndex('bigrams_author_id_lemma1_index');
            $table->dropIndex('bigrams_author_id_lemma1_lemma2_index');
        });
    }
}
