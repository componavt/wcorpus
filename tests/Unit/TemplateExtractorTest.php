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
    
    public function testGetParameterValueWithoutNames_withTemplateInside()
    {
        $wikitext = "{{poemx||{{epigraf||Vot oni — skorbnyye, gordyye teni…||[[Valeriy Yakovlevich Bryusov|V. Bryusov]]}}
Ne tol'ko pred toboyu - i predo mnoy one:
|}}";
        $expected = "{{epigraf||Vot oni — skorbnyye, gordyye teni…||[[Valeriy Yakovlevich Bryusov|V. Bryusov]]}}
Ne tol'ko pred toboyu - i predo mnoy one:";
        
        $template_name = "poemx";
        $parameter_number = 2;
        $text_result = TemplateExtractor::getParameterValueWithoutNames($template_name, $parameter_number, $wikitext);
        
        $this->assertEquals($expected, $text_result);
    }
    
    public function testGetParameterValueWithoutNames_withTemplateInsideIncorrectNumberParam()
    {
        $wikitext = "{{poemx||{{epigraf||Vot oni — skorbnyye, gordyye teni…||[[Valeriy Yakovlevich Bryusov|V. Bryusov]]}}
Ne tol'ko pred toboyu - i predo mnoy one:
}}";
        $expected = "{{epigraf||Vot oni — skorbnyye, gordyye teni…||[[Valeriy Yakovlevich Bryusov|V. Bryusov]]}}
Ne tol'ko pred toboyu - i predo mnoy one:";
        
        $template_name = "poemx";
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
    
    public function testRemoveTemplate_twice_in_begining()
    {
        $wikitext = "{{template|first param}}red and green {{template||2nd param|3rd param}}apple";
        $expected = "red and green apple";
        $text_result = TemplateExtractor::removeTemplate("template", $wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
    // -----------------------------------------------------------------
    
    public function testRemoveWikiLinks_empty()
    {
        $wikitext = "";
        $text_result = TemplateExtractor::removeWikiLinks($wikitext);
        $this->assertEquals(0, strlen($text_result));
    }
    
    public function testRemoveWikiLinks_withLink()
    {
        $wikitext = "[[Постановление ГКО № 6884с от 4.11.44|Постановлением ГОКО от 4 ноября 1944 г. № 6884с]]";
        $expected = "Постановлением ГОКО от 4 ноября 1944 г. № 6884с";
        $text_result = TemplateExtractor::removeWikiLinks($wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testRemoveWikiLinks_withoutLink()
    {
        $wikitext = "[[Постановлением ГОКО от 4 ноября 1944 г. № 6884с]]";
        $expected = "Постановлением ГОКО от 4 ноября 1944 г. № 6884с";
        $text_result = TemplateExtractor::removeWikiLinks($wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testRemoveWikiLinks_withLinkInsideText()
    {
        $wikitext = "1. Razreshit' NKO SSSR vo izmeneniye poryadka, \nustanovlennogo [[Postanovleniye GKO № 6884s ot 4.11.44|Postanovleniyem GOKO ot 4 noyabrya 1944 g. № 6884s]], napravit' dlya raboty na predpriyatiya ugol'noy promyshlennosti, chernoy metallurgii i na lesozagotovki Narkomlesa SSSR v rayony Kamskogo basseyna voyennosluzhashchikh Krasnoy Armii, osvobozhdennykh iz nemetskogo plena, proshedshikh predvaritel'nuyu registratsiyu; repatriiruyemykh sovetskikh grazhdan, priznannykh po sostoyaniyu zdorov'ya godnymi k voyennoy sluzhbe i podlezhashchikh po zakonu mobilizatsii v Krasnuyu Armiyu.";
        $expected = "1. Razreshit' NKO SSSR vo izmeneniye poryadka, \nustanovlennogo Postanovleniyem GOKO ot 4 noyabrya 1944 g. № 6884s, napravit' dlya raboty na predpriyatiya ugol'noy promyshlennosti, chernoy metallurgii i na lesozagotovki Narkomlesa SSSR v rayony Kamskogo basseyna voyennosluzhashchikh Krasnoy Armii, osvobozhdennykh iz nemetskogo plena, proshedshikh predvaritel'nuyu registratsiyu; repatriiruyemykh sovetskikh grazhdan, priznannykh po sostoyaniyu zdorov'ya godnymi k voyennoy sluzhbe i podlezhashchikh po zakonu mobilizatsii v Krasnuyu Armiyu.";
        $text_result = TemplateExtractor::removeWikiLinks($wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testRemoveWikiLinks_withLinkInsideTextManyString()
    {
        $wikitext = "В целях оказания неотложной помощи рабочей силой предприятиям угольной промышленности, черной металлургии и лесозаготовкам Наркомлеса СССР в районах Камского бассейна Государственный Комитет Обороны постановляет:

1.  Разрешить НКО СССР во изменение порядка, установленного [[Постановление ГКО № 6884с от 4.11.44|Постановлением ГОКО от 4 ноября 1944 г. № 6884с]], направить для работы на предприятия угольной промышленности, черной металлургии и на лесозаготовки Наркомлеса СССР в районы Камского бассейна военнослужащих Красной Армии, освобожденных из немецкого плена, прошедших предварительную регистрацию; репатриируемых советских граждан, признанных по состоянию здоровья годными к военной службе и подлежащих по закону мобилизации в Красную Армию.

2.  Обязать НКО СССР (т. Смородинова) в соответствии с пунктом 1 настоящего постановления направить до 1 ноября 1945 г. 360 тыс. человек.";
        $expected = "В целях оказания неотложной помощи рабочей силой предприятиям угольной промышленности, черной металлургии и лесозаготовкам Наркомлеса СССР в районах Камского бассейна Государственный Комитет Обороны постановляет:

1.  Разрешить НКО СССР во изменение порядка, установленного Постановлением ГОКО от 4 ноября 1944 г. № 6884с, направить для работы на предприятия угольной промышленности, черной металлургии и на лесозаготовки Наркомлеса СССР в районы Камского бассейна военнослужащих Красной Армии, освобожденных из немецкого плена, прошедших предварительную регистрацию; репатриируемых советских граждан, признанных по состоянию здоровья годными к военной службе и подлежащих по закону мобилизации в Красную Армию.

2.  Обязать НКО СССР (т. Смородинова) в соответствии с пунктом 1 настоящего постановления направить до 1 ноября 1945 г. 360 тыс. человек.";
        $text_result = TemplateExtractor::removeWikiLinks($wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
    // -----------------------------------------------------------------
    
    public function testsplitIntoSentences_empty()
    {
        $text = "";
        $expected = [];
        $text_result = TemplateExtractor::splitIntoSentences($text);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testsplitIntoSentences_simple()
    {
        $text = "Это было в Черном море в ноябре месяце. Русская парусная шхуна «Мария» под командой хозяина Афанасия Нечепуренки шла в Болгарию с грузом жмыхов в трюме. Была ночь, и дул свежий ветер с востока, холодный и с дождем. Ветер был почти попутный.";
        $expected = ["Это было в Черном море в ноябре месяце.",
            "Русская парусная шхуна «Мария» под командой хозяина Афанасия Нечепуренки шла в Болгарию с грузом жмыхов в трюме.", 
            "Была ночь, и дул свежий ветер с востока, холодный и с дождем.",
            "Ветер был почти попутный."];
        $text_result = TemplateExtractor::splitIntoSentences($text);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testsplitIntoSentences_poetry()
    {
        $text = "Drug moy, drug moy,\nYA ochen' i ochen' bolen.\nSam ne znayu, otkuda vzyalas' eta bol'.\nTo li veter svistit\nNad pustym i bezlyudnym polem,\nTo l', kak roshchu v sentyabr',\nOsypayet mozgi alkogol'.";
        $expected = ["Drug moy, drug moy, YA ochen' i ochen' bolen.",
            "Sam ne znayu, otkuda vzyalas' eta bol'.",
            "To li veter svistit Nad pustym i bezlyudnym polem, To l', kak roshchu v sentyabr', Osypayet mozgi alkogol'."];
        $text_result = TemplateExtractor::splitIntoSentences($text);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testsplitIntoSentences_directSpeech()
    {
        $text = "- Mozhet byt', vy otpravites' so mnoy v konsul'stvo, kapitan? Vy uspokoites' i ob`yasnites', — skazal nakonets konsul.";
        $expected = ["Mozhet byt', vy otpravites' so mnoy v konsul'stvo, kapitan?",
            "Vy uspokoites' i ob`yasnites', — skazal nakonets konsul."];
        $text_result = TemplateExtractor::splitIntoSentences($text);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testsplitIntoSentences_directSpeechLongDash()
    {
        $text = "— Mozhet byt', vy otpravites' so mnoy v konsul'stvo, kapitan? Vy uspokoites' i ob`yasnites', — skazal nakonets konsul.";
        $expected = ["Mozhet byt', vy otpravites' so mnoy v konsul'stvo, kapitan?",
            "Vy uspokoites' i ob`yasnites', — skazal nakonets konsul."];
        $text_result = TemplateExtractor::splitIntoSentences($text);
        $this->assertEquals($expected, $text_result);
    }
    
    
    public function testsplitIntoSentences_directSpeechLongDash2()
    {
        $text = "— Ремонт, — сказал Паркер. — Он с трудом переводил дух, и консулу жалко было смотреть, как волновался этот человек. — Маленький… ремонт, сэр… в доке.";
        $expected = ["Ремонт, — сказал Паркер.",
            "Он с трудом переводил дух, и консулу жалко было смотреть, как волновался этот человек.",
            "Маленький… ремонт, сэр… в доке."];
        $text_result = TemplateExtractor::splitIntoSentences($text);
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
        $text_result = TemplateExtractor::splitIntoParagraphs($text);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testSplitIntoParagraphs_simple()
    {
        $text = "Drug moy, drug moy.
            
The end.";

        $expected = [
            "Drug moy, drug moy.",
            
            "The end."];

        $text_result = TemplateExtractor::splitIntoParagraphs($text);
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

        $text_result = TemplateExtractor::splitIntoParagraphs($text);
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

        $text_result = TemplateExtractor::splitIntoParagraphs($text);
        $this->assertEquals($expected, $text_result);
    }
    
    // -----------------------------------------------------------------
    
    public function testsplitIntoWords_empty()
    {
        $text = "";
        $expected = [];
        $text_result = TemplateExtractor::splitIntoParagraphs($text);
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
        $text_result = TemplateExtractor::splitIntoWords($text);
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
        $text_result = TemplateExtractor::splitIntoWords($text);
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
        $text_result = TemplateExtractor::splitIntoWords($text);
        $this->assertEquals($expected, $text_result);
    }
    
}
