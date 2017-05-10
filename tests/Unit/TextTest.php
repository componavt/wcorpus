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
        
        $this->assertEquals($expected, $text_result);
    }
    
    public function testParseWikitext_poemxWithEpigraph()
    {
        $wikitext = "{{Otekste
| AVTOR = Nadezhda Grigor'yevna L'vova (1891—1913) 
| NAZVANIYe = «…Ne tol'ko pred toboyu — i predo mnoy one…»
}}{{poemx||{{epigraf||Vot oni — skorbnyye, gordyye teni…||Valeriy Yakovlevich Bryusov|V. Bryusov}}
Ne tol'ko pred toboyu — i predo mnoy one:
}}";
        
        $expected =
                ['text'=>"Ne tol'ko pred toboyu — i predo mnoy one:",
                 'title' => '',
                 'creation_date' => ''];
        $text = new Text();
        $text_result = $text->parseWikitext( $wikitext );
        $this->assertEquals($expected, $text_result);
    }
    
    // -----------------------------------------------------------------
    
    public function testsplitIntoSentences_empty()
    {
        $text = "";
        $expected = [];
        $text_result = Text::splitIntoSentences($text);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testsplitIntoSentences_simple()
    {
        $text = "Это было в Черном море в ноябре месяце. Русская парусная шхуна «Мария» под командой хозяина Афанасия Нечепуренки шла в Болгарию с грузом жмыхов в трюме. Была ночь, и дул свежий ветер с востока, холодный и с дождем. Ветер был почти попутный.";
        $expected = ["Это было в Черном море в ноябре месяце.",
            "Русская парусная шхуна «Мария» под командой хозяина Афанасия Нечепуренки шла в Болгарию с грузом жмыхов в трюме.", 
            "Была ночь, и дул свежий ветер с востока, холодный и с дождем.",
            "Ветер был почти попутный."];
        $text_result = Text::splitIntoSentences($text);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testsplitIntoSentences_poetry()
    {
        $text = "Drug moy, drug moy,\nYA ochen' i ochen' bolen.\nSam ne znayu, otkuda vzyalas' eta bol'.\nTo li veter svistit\nNad pustym i bezlyudnym polem,\nTo l', kak roshchu v sentyabr',\nOsypayet mozgi alkogol'.";
        $expected = ["Drug moy, drug moy, YA ochen' i ochen' bolen.",
            "Sam ne znayu, otkuda vzyalas' eta bol'.",
            "To li veter svistit Nad pustym i bezlyudnym polem, To l', kak roshchu v sentyabr', Osypayet mozgi alkogol'."];
        $text_result = Text::splitIntoSentences($text);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testsplitIntoSentences_directSpeech()
    {
        $text = "- Mozhet byt', vy otpravites' so mnoy v konsul'stvo, kapitan? Vy uspokoites' i ob`yasnites', — skazal nakonets konsul.";
        $expected = ["Mozhet byt', vy otpravites' so mnoy v konsul'stvo, kapitan?",
            "Vy uspokoites' i ob`yasnites', — skazal nakonets konsul."];
        $text_result = Text::splitIntoSentences($text);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testsplitIntoSentences_directSpeechLongDash()
    {
        $text = "— Mozhet byt', vy otpravites' so mnoy v konsul'stvo, kapitan? Vy uspokoites' i ob`yasnites', — skazal nakonets konsul.";
        $expected = ["Mozhet byt', vy otpravites' so mnoy v konsul'stvo, kapitan?",
            "Vy uspokoites' i ob`yasnites', — skazal nakonets konsul."];
        $text_result = Text::splitIntoSentences($text);
        $this->assertEquals($expected, $text_result);
    }
    
    
    public function testsplitIntoSentences_directSpeechLongDash2()
    {
        $text = "— Ремонт, — сказал Паркер. — Он с трудом переводил дух, и консулу жалко было смотреть, как волновался этот человек. — Маленький… ремонт, сэр… в доке.";
        $expected = ["Ремонт, — сказал Паркер.",
            "Он с трудом переводил дух, и консулу жалко было смотреть, как волновался этот человек.",
            "Маленький… ремонт, сэр… в доке."];
        $text_result = Text::splitIntoSentences($text);
        $this->assertEquals($expected, $text_result);
    }
/*    
    public function testsplitIntoSentences_PointWithinSentence()
    {
        $text = "1. Razreshit' NKO SSSR vo izmeneniye poryadka, ustanovlennogo Postanovleniyem GOKO ot 4 noyabrya 1944 g. № 6884s, napravit' dlya raboty na predpriyatiya ugol'noy promyshlennosti, chernoy metallurgii i na lesozagotovki Narkomlesa SSSR v rayony Kamskogo basseyna voyennosluzhashchikh Krasnoy Armii, osvobozhdennykh iz nemetskogo plena, proshedshikh predvaritel'nuyu registratsiyu; repatriiruyemykh sovetskikh grazhdan, priznannykh po sostoyaniyu zdorov'ya godnymi k voyennoy sluzhbe i podlezhashchikh po zakonu mobilizatsii v Krasnuyu Armiyu.";
        $expected = ["1. Razreshit' NKO SSSR vo izmeneniye poryadka, ustanovlennogo Postanovleniyem GOKO ot 4 noyabrya 1944 g. № 6884s, napravit' dlya raboty na predpriyatiya ugol'noy promyshlennosti, chernoy metallurgii i na lesozagotovki Narkomlesa SSSR v rayony Kamskogo basseyna voyennosluzhashchikh Krasnoy Armii, osvobozhdennykh iz nemetskogo plena, proshedshikh predvaritel'nuyu registratsiyu; repatriiruyemykh sovetskikh grazhdan, priznannykh po sostoyaniyu zdorov'ya godnymi k voyennoy sluzhbe i podlezhashchikh po zakonu mobilizatsii v Krasnuyu Armiyu."];
        $text_result = TemplateExtractor::splitIntoSentences($text);
        $this->assertEquals($expected, $text_result);
    }

    public function testsplitIntoSentences_WithinAbbr()
    {
        $text = "Vopros zdes' ne v tom, skol'ko dney ili let vy uchite tot ili inoy yazyk, vopros v tom, chto vam real'no nuzhno obuchit' programmu ponimat' tekst. Konkretnyy yazyk programmirovaniya tut ne pri chom, eto vopros teorii. Vy ne mozhete po-logkomu, na osnove formal'nykh kriteriyev, otlichit' konets predlozheniya ot sokrashcheniya. Sravnite, naprimer: «V dueli uchastvovali g. Pushkin i g. Dantes» i «Moi stikhi — odno sploshnoye g. Pushkin by zastrelilsya, no ne stal chitat' takoye.";
        $expected = ["Vopros zdes' ne v tom, skol'ko dney ili let vy uchite tot ili inoy yazyk, vopros v tom, chto vam real'no nuzhno obuchit' programmu ponimat' tekst.", 
            "Konkretnyy yazyk programmirovaniya tut ne pri chom, eto vopros teorii.",
            "Vy ne mozhete po-logkomu, na osnove formal'nykh kriteriyev, otlichit' konets predlozheniya ot sokrashcheniya.", 
            "Sravnite, naprimer: «V dueli uchastvovali g. Pushkin i g. Dantes» i «Moi stikhi — odno sploshnoye g. ",
            "Pushkin by zastrelilsya, no ne stal chitat' takoye."];
        $text_result = TemplateExtractor::splitIntoSentences($text);
        $this->assertEquals($expected, $text_result);
    }
*/
    // -----------------------------------------------------------------
    
    public function testsplitIntoParagraphs_empty()
    {
        $text = "";
        $expected = [];
        $text_result = Text::splitIntoParagraphs($text);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testSplitIntoParagraphs_simple()
    {
        $text = "Drug moy, drug moy.
            
The end.";

        $expected = [
            "Drug moy, drug moy.",
            
            "The end."];

        $text_result = Text::splitIntoParagraphs($text);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testSplitIntoParagraphs_poetry()
    {
        $text = "Drug moy, drug moy,
YA ochen' i ochen' bolen.

Golova moya mashet ushami,
Kak kryl'yami ptitsa.";
      
        $expected = [
            "Drug moy, drug moy,\nYA ochen' i ochen' bolen.",

"Golova moya mashet ushami,\nKak kryl'yami ptitsa."];

        $text_result = Text::splitIntoParagraphs($text);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testSplitIntoParagraphs_longPoetry()
    {
        $text = "Drug moy, drug moy,
YA ochen' i ochen' bolen.
Sam ne znayu, otkuda vzyalas' eta bol'.
To li veter svistit
Nad pustym i bezlyudnym polem,
To l', kak roshchu v sentyabr',
Osypayet mozgi alkogol'.

Golova moya mashet ushami,
Kak kryl'yami ptitsa.
Yey na sheye nogi
Mayachit' bol'she nevmoch'.
Chernyy chelovek,
Chernyy, chernyy,
Chernyy chelovek
Na krovat' ko mne saditsya,
Chernyy chelovek
Spat' ne dayet mne vsyu noch'.";
      
        $expected = [
            // first paragraph
            "Drug moy, drug moy,\nYA ochen' i ochen' bolen.\nSam ne znayu, otkuda vzyalas' eta bol'.\nTo li veter svistit\nNad pustym i bezlyudnym polem,\nTo l', kak roshchu v sentyabr',\nOsypayet mozgi alkogol'.",
            
            // second paragraph
"Golova moya mashet ushami,\nKak kryl'yami ptitsa.\nYey na sheye nogi\nMayachit' bol'she nevmoch'.\nChernyy chelovek,\nChernyy, chernyy,\nChernyy chelovek\nNa krovat' ko mne saditsya,\nChernyy chelovek\nSpat' ne dayet mne vsyu noch'."];

        $text_result = Text::splitIntoParagraphs($text);
        $this->assertEquals($expected, $text_result);
    }
    
    // -----------------------------------------------------------------
    
    public function testsplitIntoWords_empty()
    {
        $text = "";
        $expected = [];
        $text_result = Text::splitIntoParagraphs($text);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testsplitIntoWords_simple()
    {
        $text = "Это было в Черном море в ноябре месяце.";
        $expected = ["Это",
                     "было",
                     "в",
                     "Черном",
                     "море",
                     "в",
                     "ноябре",
                     "месяце"
                    ];
        $text_result = Text::splitIntoWords($text);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testsplitIntoWords_withDash()
    {
        $text = "И всякую ночь,  около  полуночи,  я  поднимал щеколду и приотворял его дверь - тихо-тихо!";
        $expected = ["И",
            "всякую",
            "ночь",  
            "около",  
            "полуночи",  
            "я",
            "поднимал",
            "щеколду", 
            "и", 
            "приотворял", 
            "его", 
            "дверь",
            "тихо-тихо"
                    ];
        $text_result = Text::splitIntoWords($text);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testsplitIntoWords_withApostrophe()
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
        $text_result = Text::splitIntoWords($text);
        $this->assertEquals($expected, $text_result);
    }
    
    
}
