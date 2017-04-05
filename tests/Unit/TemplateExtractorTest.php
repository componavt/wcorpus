<?php

namespace Tests\Unit;

use Wcorpus\Wikiparser\TemplateExtractor;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TemplateExtractorTest extends TestCase
{
    
    public function testGetParameterValueWithoutNames_empty()
    {
        $wikitext = "";
        
        $template_name = "Poemx";
        $parameter_number = 2;
        $text_result = TemplateExtractor::getParameterValueWithoutNames($template_name, $parameter_number, $wikitext);
        
        $this->assertEquals(0, strlen($text_result));
    }
    
    // {{Poemx|first param|2nd param|3rd param}},
    // extracts a text of second parameter from the template above
    public function testGetParameterValueWithoutNames_simple()
    {
        $wikitext = "{{Poemx|first param|2nd param|3rd param}}";
        $expected = "2nd param";
        
        $template_name = "Poemx";
        $parameter_number = 2;
        $text_result = TemplateExtractor::getParameterValueWithoutNames($template_name, $parameter_number, $wikitext);
        
        $this->assertEquals($expected, $text_result);
    }
    
    // -----------------------------------------------------------------
    
    public function testRemoveTemplate_simple()
    {
        $wikitext = "red {{Poemx|first param|2nd param|3rd param}}apple";
        $expected = "red apple";
        $text_result = TemplateExtractor::removeTemplate("Poemx", $wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testRemoveTemplate_twice()
    {
        $wikitext = "red {{template|first param}}and green {{template||2nd param|3rd param}}apple";
        $expected = "red and green apple";
        $text_result = TemplateExtractor::removeTemplate("template", $wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
}
