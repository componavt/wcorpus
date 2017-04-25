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
/*    
    public function testsplitIntoSentences_simple()
    {
        $text = "Друг мой, друг мой,
Я очень и очень болен.
Сам не знаю, откуда взялась эта боль.
То ли ветер свистит
Над пустым и безлюдным полем,
То ль, как рощу в сентябрь,
Осыпает мозги алкоголь.";
        $expected = ["Друг мой, друг мой, Я очень и очень болен",
"Сам не знаю, откуда взялась эта боль",
"То ли ветер свистит Над пустым и безлюдным полем, То ль, как рощу в сентябрь, Осыпает мозги алкоголь"];
        $text_result = TemplateExtractor::splitIntoSentences($text);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testsplitIntoSentences_PointWithinSentence()
    {
        $text = "В целях оказания неотложной помощи рабочей силой предприятиям угольной промышленности, черной металлургии и лесозаготовкам Наркомлеса СССР в районах Камского бассейна Государственный Комитет Обороны постановляет:

1.  Разрешить НКО СССР во изменение порядка, установленного Постановлением ГОКО от 4 ноября 1944 г. № 6884с, направить для работы на предприятия угольной промышленности, черной металлургии и на лесозаготовки Наркомлеса СССР в районы Камского бассейна военнослужащих Красной Армии, освобожденных из немецкого плена, прошедших предварительную регистрацию; репатриируемых советских граждан, признанных по состоянию здоровья годными к военной службе и подлежащих по закону мобилизации в Красную Армию.

2.  Обязать НКО СССР (т. Смородинова) в соответствии с пунктом 1 настоящего постановления направить до 1 ноября 1945 г. 360 тыс. человек.";
        $expected = ["В целях оказания неотложной помощи рабочей силой предприятиям угольной промышленности, черной металлургии и лесозаготовкам Наркомлеса СССР в районах Камского бассейна Государственный Комитет Обороны постановляет:",
"1.  Разрешить НКО СССР во изменение порядка, установленного Постановлением ГОКО от 4 ноября 1944 г. № 6884с, направить для работы на предприятия угольной промышленности, черной металлургии и на лесозаготовки Наркомлеса СССР в районы Камского бассейна военнослужащих Красной Армии, освобожденных из немецкого плена, прошедших предварительную регистрацию; репатриируемых советских граждан, признанных по состоянию здоровья годными к военной службе и подлежащих по закону мобилизации в Красную Армию",
"2.  Обязать НКО СССР (т. Смородинова) в соответствии с пунктом 1 настоящего постановления направить до 1 ноября 1945 г. 360 тыс. человек."];
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
    
    /*
     * 
Вопрос здесь не в том, сколько дней или лет вы учите тот или иной язык, вопрос в том, что вам реально нужно обучить программу понимать текст. Конкретный язык программирования тут не при чём, это вопрос теории. Вы не можете по-лёгкому, на основе формальных критериев, отличить конец предложения от сокращения. Сравните, например: «В дуэли участвовали г. Пушкин и г. Дантес» и «Мои стихи — одно сплошное г. Пушкин бы застрелился, но не стал читать такое.     
     */
}
