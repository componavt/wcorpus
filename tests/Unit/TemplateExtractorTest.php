<?php

namespace Tests\Unit;

use Wcorpus\Wikiparser\TemplateExtractor;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TemplateExtractorTest extends TestCase
{
    
    public function testGetParameterValue_empty()
    {
        $wikitext = "";
        
        $template_name = "Poemx";
        $parameter_number = 2;
        $text_result = TemplateExtractor::getParameterValue($template_name, $parameter_number, $wikitext);
        
        $this->assertEquals(0, strlen($text_result));
    }
    
    // {{Poemx|1|2|3}},
    // extracts a text of second parameter from the template {{Poemx|1|2|3}}
}
