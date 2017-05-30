<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Wcorpus\Models\Wordform;

// This file was created by:
// php artisan make:test TextTest --unit

class WordformTest extends TestCase
{
    public function testLemmatize_empty()
    {
        $word = "";
        $expected = '';
        $wordform = new Wordform();
        $text_result = $wordform->lemmatize();
        $this->assertEquals($expected, $text_result);
    }

    public function testLemmatize_simple()
    {
        $wordform = new Wordform();
        $wordform->wordform = "духов";
        $expected = [
            ['lemma'=>"ДУХ",'pos'=>'С','dictionary'=>'1'],
            ['lemma'=>"ДУХОВ",'pos'=>'С','dictionary'=>'1'],
            ['lemma'=>"ДУХИ",'pos'=>'С','dictionary'=>'1']
        ];
        $text_result = $wordform->lemmatize();
print_r($text_result);
        $this->assertEquals($expected, $text_result);
    }

/*    public function testLemmatize_nonExistingWord()
    {
        $word = "капустача";    // жуков
        $expected = ["КАПУСТАЧА"]; // жук
        $text_result = Lemma::lemmatize($word);
//print_r($text_result);
        $this->assertEquals($expected, $text_result);
    }
*/
    // -----------------------------------------------------------------
    

}
