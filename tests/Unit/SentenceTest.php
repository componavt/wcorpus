<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Wcorpus\Models\Sentence;

// This file was created by:
// php artisan make:test TextTest --unit

class SentenceTest extends TestCase
{
    public function testSplitIntoWords_empty()
    {
        $text = "";
        $expected = [];
        $text_result = Sentence::splitIntoWords($text);
        $this->assertEquals($expected, $text_result);
    }

    public function testSplitIntoWords_simple()
    {
        $text = "Матушка, Варвара Алексеевна!";    
        $expected = ["Матушка", "Варвара", "Алексеевна"]; 
        $text_result = Sentence::SplitIntoWords($text);

        $this->assertEquals($expected, $text_result);
    }

    public function testSplitIntoWords_withDash()
    {
        $text = "Я этому верю, Варенька, и в доброту ангельского сердечка вашего верю, и не в укор вам говорю, — только не попрекайте меня, как тогда, что я на старости лет замотался.";    
//        $text = "Он, бедный-то человек, он взыскателен; он и на свет-то Божий иначе смотрит";
        $expected = ["Я", "этому", "верю", "Варенька", "и", "в", "доброту", "ангельского", "сердечка", "вашего", "верю", "и", "не", "в", "укор", "вам", "говорю", "только", "не", "попрекайте", "меня", "как", "тогда", "что", "я", "на", "старости", "лет", "замотался"]; 
//        $expected = ["Он", "бедный-то", "человек", "он", "взыскателен", "он", "и", "на", "свет-то", "Божий", "иначе", "смотрит"];
        $text_result = Sentence::SplitIntoWords($text);

        $this->assertEquals($expected, $text_result);
    }

    public function testSplitIntoWords_withApostrophe()
    {
        $text = "A watch's minute hand moves more quickly than did mine.";
        $expected = ["A",
            "watch's",
            "minute",
            "hand",
            "moves",
            "more",
            "quickly",
            "than",
            "did",
            "mine"
                    ];
        $text_result = Sentence::splitIntoWords($text);
        $this->assertEquals($expected, $text_result);
    }

    public function testSplitIntoWords_noWords()
    {
        $text = "— 14… 15… 16…";    
        $expected = []; 
        $text_result = Sentence::SplitIntoWords($text);

        $this->assertEquals($expected, $text_result);
    }

/*   
 * цифры удаляются
 *  
    public function testSplitIntoWords_withNumbers()
    {
        $text = "Апполон-17, 2-рядная гармошка";
        $expected = ["Апполон-17", "2-рядная", "гармошка"];
        $text_result = Sentence::splitIntoWords($text);
        $this->assertEquals($expected, $text_result);
    }
 * 
 */
    // -----------------------------------------------------------------
    

}
