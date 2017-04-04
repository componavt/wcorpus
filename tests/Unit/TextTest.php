<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Wcorpus\Models\Text;

// This file was created by:
// php artisan make:test TextTest --unit

class TextTest extends TestCase
{
    
    public function testParseWikitext_Empty()
    {
        $wikitext = "";
        $text = new Text();
        $array_result = $text->parseWikitext( $wikitext );
        $text_result  = $array_result['text'];
        
        $this->assertEquals(0, strlen($text_result));
    }
    
    // {{Poemx|1|2|3}}, 2 is text, see https://ru.wikisource.org/wiki/template:Poemx
    public function testParseWikitext_poemxWithTitle()
    {
        $wikitext = "{{poemx|?|
Пусть для ваших открытых сердец
...
Только бледной улыбкой поводит.|}}";
        
        $expected = "Пусть для ваших открытых сердец
...
Только бледной улыбкой поводит.";
        
        $text = new Text();
        $array_result = $text->parseWikitext( $wikitext );
        $text_result  = $array_result['text'];
        
// todo        $this->assertEquals($expected, $text_result);
    }
    
    
    
}
