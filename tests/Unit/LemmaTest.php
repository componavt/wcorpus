<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Wcorpus\Models\Lemma;

// This file was created by:
// php artisan make:test TextTest --unit

class LemmaTest extends TestCase
{
    public function testLemmatize_empty()
    {
        $word = "";
        $expected = '';
        $text_result = Lemma::lemmatize($word);
        $this->assertEquals($expected, $text_result);
    }

    public function testLemmatize_simple()
    {
        $word = "жуков";
        $expected = 'жук';
        $text_result = Lemma::lemmatize($word);
        $this->assertEquals($expected, $text_result);
    }

    // -----------------------------------------------------------------
    

}
