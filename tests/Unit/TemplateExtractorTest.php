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
    
    public function testRemoveComments_empty()
    {
        $wikitext = "";
        $text_result = TemplateExtractor::removeComments($wikitext);
        $this->assertEquals(0, strlen($text_result));
    }
    
    public function testRemoveComments_onlyComments()
    {
        $wikitext = "<!--Постановление ГКО № 6884с от 4.11.44|Постановлением ГОКО от 4 ноября 1944 г. № 6884с-->";
        $expected = "";
        $text_result = TemplateExtractor::removeComments($wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testRemoveComments_inBegining()
    {
        $wikitext = "<!--Постановление ГКО № 6884с от 4.11.44|Постановлением ГОКО от 4 ноября 1944 г. № 6884с-->text";
        $expected = "text";
        $text_result = TemplateExtractor::removeComments($wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testRemoveComments_inEnding()
    {
        $wikitext = "text<!--Постановление ГКО № 6884с от 4.11.44|Постановлением ГОКО от 4 ноября 1944 г. № 6884с-->";
        $expected = "text";
        $text_result = TemplateExtractor::removeComments($wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testRemoveComments_inside()
    {
        $wikitext = "start text<!--Постановление ГКО № 6884с от 4.11.44|Постановлением ГОКО от 4 ноября 1944 г. № 6884с-->end text";
        $expected = "start textend text";
        $text_result = TemplateExtractor::removeComments($wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testRemoveComments_beginningCommentInside()
    {
        $wikitext = "start text<!--Постановление <!--ГКО № 6884с от 4.11.44|Постановлением ГОКО от 4 ноября 1944 г. № 6884с-->end text";
        $expected = "start textend text";
        $text_result = TemplateExtractor::removeComments($wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testRemoveComments_commentInside()
    {
        $wikitext = "start text<!--Постановление <!--ГКО № 6884с от 4.11.44|Постановлением--> ГОКО от 4 ноября 1944 г. № 6884с-->end text";
        $expected = "start text ГОКО от 4 ноября 1944 г. № 6884с-->end text";
        $text_result = TemplateExtractor::removeComments($wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testRemoveComments_moreOneComments()
    {
        $wikitext = "start text<!--Постановление--> \n<!--ГКО № 6884с от 4.11.44|Постановлением--> ГОКО от 4 ноября 1944<!-- г. № 6884с-->end text";
        $expected = "start text \n ГОКО от 4 ноября 1944end text";
        $text_result = TemplateExtractor::removeComments($wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testRemoveComments_withoutEnd()
    {
        $wikitext = "start text<!--Постановление--> \n<!--ГКО № 6884с от 4.11.44|Постановлением--> ГОКО от 4 ноября 1944<!-- г. № 6884с end text";
        $expected = "start text \n ГОКО от 4 ноября 1944";
        $text_result = TemplateExtractor::removeComments($wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
    // -----------------------------------------------------------------
    
    public function testRemoveLangTemplates_empty()
    {
        $wikitext = "";
        $text_result = TemplateExtractor::removeLangTemplates($wikitext);
        $this->assertEquals(0, strlen($text_result));
    }
    
    public function testRemoveLangTemplates_normal()
    {
        $wikitext = "{{lang|en|season}}";
        $expected = "season";
        $text_result = TemplateExtractor::removeLangTemplates($wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testRemoveLangTemplates_utf()
    {
        $wikitext = "{{lang|he|והיה כי יארכו הימים}}";
        $expected = "והיה כי יארכו הימים";
        $text_result = TemplateExtractor::removeLangTemplates($wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testRemoveLangTemplates_brief()
    {
        $wikitext = "{{lang-en|season}}";
        $expected = "season";
        $text_result = TemplateExtractor::removeLangTemplates($wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testRemoveLangTemplates_emptyText()
    {
        $wikitext = "{{lang|it|Maria Santissima del Divin Patre.}}<ref>{{lang-it|}}</ref>";
        $expected = "Maria Santissima del Divin Patre.<ref></ref>";
        $text_result = TemplateExtractor::removeLangTemplates($wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
    // -----------------------------------------------------------------
    
    public function testExtractPoetry_empty()
    {
        $initial =
        $expected =
                ['text'=>'',
                 'title' => null,
                 'creation_date' => null];
        $text_result = TemplateExtractor::extractPoetry($initial);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testExtractPoetry_normal()
    {
        $initial =
                ['text'=>'{{poemx|?|
Пусть для ваших открытых сердец
До сих пор это — светлая фея
С упоительной лирой Орфея,
Для меня это — старый мудрец.

По лицу его тяжко проходит
Бороздой Вековая Мечта,
И для мира немые уста
Только бледной улыбкой поводит.|}}',
                 'title' => null,
                 'creation_date' => null];
        
        $expected =
                ['text'=>'Пусть для ваших открытых сердец
До сих пор это — светлая фея
С упоительной лирой Орфея,
Для меня это — старый мудрец.

По лицу его тяжко проходит
Бороздой Вековая Мечта,
И для мира немые уста
Только бледной улыбкой поводит.',
                 'title' => '?',
                 'creation_date' => ''];
        $text_result = TemplateExtractor::extractPoetry($initial);
        $this->assertEquals($expected, $text_result);
    }
    
    // -----------------------------------------------------------------
    
    public function testRemoveAnyTemplates_empty()
    {
        $wikitext = "";
        $text_result = TemplateExtractor::removeAnyTemplates($wikitext);
        $this->assertEquals(0, strlen($text_result));
    }
    
    public function testRemoveAnyTemplates_normal()
    {
        $wikitext = "aaa {{lang|en|season}}bbb";
        $expected = "aaa bbb";
        $text_result = TemplateExtractor::removeAnyTemplates($wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
    public function testRemoveAnyTemplates_nested()
    {
        $wikitext = "{{Otekste
| NAZVANIYe=?
| PODZAGOLOVOK=«Pust' dlya vashikh otkrytykh serdets…»
| AVTOR=Innokentiy Fodorovich Annenskiy (1856—1909)
| SODERZHANIYe=
| IZTSIKLA=
| IZSBORNIKA=Tikhiye pesni
| DATASOZDANIYA=
| DATAPUBLIKATSII = 1904Vpervyye — v knige {{Annenskiy:Tikhiye pesni, 1904|stranitsy=29}}.
| ISTOCHNIK={{Annenskiy:Izbrannyye proizvedeniya, 1988|stranitsy=44}}.
| VIKIPEDIYA=
| DRUGOYe=
| PREDYDUSHCHIY=Tam
| SLEDUYUSHCHIY=Pervyy fortep'yannyy sonet
| KACHESTVO=4
}}

Пусть для ваших открытых сердец
До сих пор это — светлая фея
С упоительной лирой Орфея,
Для меня это — старый мудрец.

По лицу его тяжко проходит
Бороздой Вековая Мечта,
И для мира немые уста
Только бледной улыбкой поводит.

== Примечания ==
{{примечания}}";
        $expected = "Пусть для ваших открытых сердец
До сих пор это — светлая фея
С упоительной лирой Орфея,
Для меня это — старый мудрец.

По лицу его тяжко проходит
Бороздой Вековая Мечта,
И для мира немые уста
Только бледной улыбкой поводит.

== Примечания ==";
        $text_result = TemplateExtractor::removeAnyTemplates($wikitext);
        $this->assertEquals($expected, $text_result);
    }
    
}
