<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Wcorpus\Models\Author;

// This file was created by:
// php artisan make:test TextTest --unit

class AuthorTest extends TestCase
{
    
    public function testSearchAuthorName_Empty()
    {
        $wikitext = "";
        $expected  = null;
        $text_result = Author::searchAuthorName( $wikitext );
        
        $this->assertEquals($expected, $text_result);
    }

    public function testSearchAuthorName_withYears()
    {
        $wikitext = "{{О тексте
| АВТОР          = Александр Сергеевич Пушкин (1799—1837)
| НАЗВАНИЕ       = Для берегов отчизны дальной…
| ДАТАСОЗДАНИЯ   = 1830<ref>Датируется, согласно автографу, 27 ноября 1830 г.; вероятно, это—дата окончательной отделки, и стихотворение написано в 1828 г.</ref>
| ДАТАПУБЛИКАЦИИ = 1841<ref>Опубликовано по копии, сообщенной вероятно Плетневым, Владиславлевым, в альманахе «Утреняя Заря на 1841 год» под заглавием «Разлука».</ref>
}}

\{\{poemx|Для берегов отчизны дальной…|
Для берегов отчизны дальной";
        $expected  = "Александр Сергеевич Пушкин";
        $text_result = Author::searchAuthorName( $wikitext );
//print       "\n$text_result\n";  
        $this->assertEquals($expected, $text_result);
    }
    
/*    
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
    
}
