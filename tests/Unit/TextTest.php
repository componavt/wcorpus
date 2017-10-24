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
    
    public function testParseWikitext_poemWithTag()
    {
        $wikitext = "{{Otekste
| KACHESTVO = 3
| NAZVANIYe = «Zachem vy, dni?» — skazal poet…
| AVTOR = [[Potr Andreyevich Vyazemskiy]] (1792—1878)
}}
{{poem||<poem>
«Zachem vy, dni?» — skazal poet.<ref>Boratynskiy. — ''Prim. avt.'' Netochnaya tsitata iz stikhotvoreniya Boratynskogo «Na chto vy, dni! Yudol'nyy mir yavlen'ya…» (1840?).</ref>
A ya sproshu: «Zachem vy, nochi?»
Zachem vash mrak sgonyayet svet</poem>|1863 ili 1864}}";
        
        $expected =
                ['text'=>"«Zachem vy, dni?» — skazal poet.
A ya sproshu: «Zachem vy, nochi?»
Zachem vash mrak sgonyayet svet",
                 'title' => null,
                 'creation_date' => '1863 ili 1864'];
        $text = new Text();
        $text_result = $text->parseWikitext( $wikitext );
        $this->assertEquals($expected, $text_result);
    }
    
    public function testParseWikitext_poem_on()
    {
        $wikitext = "{{Otekste
|KACHESTVO=75%
|AVTOR=Mikhail Alekseyevich Kuzmin (1876—1936)
|NAZVANIYe=«A eto — khuliganskaya», — skazala… 
}}

{{poem-on|* * *}}<poem>
''O. A. Glebovoy-Sudeykinoy''

«A eto — khuliganskaya», — skazala
Priyatel'nitsa milaya, starayas'
Oslablennomu golosu pridat'
Ves' dikiy romantizm polnochnykh rek,
{{nr|5}} Vso udal'stvo, lyubov' i beznadezhnost',
Ves' gor'kiy khmel' tragicheskikh svidaniy.
I dal'niy klokot slushali, potupyas',
Tut romanist, poet i kompozitor,
A tyulevaya noch' v okne dremala,
</poem>{{poem-off|<iyun'> 1922}}";
        
        $expected =
                ['text'=>"''O. A. Glebovoy-Sudeykinoy''

«A eto — khuliganskaya», — skazala
Priyatel'nitsa milaya, starayas'
Oslablennomu golosu pridat'
Ves' dikiy romantizm polnochnykh rek,
 Vso udal'stvo, lyubov' i beznadezhnost',
Ves' gor'kiy khmel' tragicheskikh svidaniy.
I dal'niy klokot slushali, potupyas',
Tut romanist, poet i kompozitor,
A tyulevaya noch' v okne dremala,",
                 'title' => '* * *',
                 'creation_date' => "<iyun'> 1922"];
        $text = new Text();
        $text_result = $text->parseWikitext( $wikitext );
        $this->assertEquals($expected, $text_result);
    }
    
    public function testParseWikitext_nestedTemplates()
    {
        $wikitext = "{{Otekste
| AVTOR = Vlas Mikhaylovich Doroshevich
| NAZVANIYe = «25 let vladychestva nad mirom»
| PODZAGOLOVOK = Yubiley papy
| IZTSIKLA =
| DATASOZDANIYA =
| DATAPUBLIKATSII =
| ISTOCHNIK = {{Sobraniye sochineniy. Tom V. Po Yevrope|197}}
| DRUGOYe =
| VIKIPEDIYA =
| IZOBRAZHENIYe =
| KACHESTVO =
}}

== I ==


Nad Rimom navisli tomnyye tuchi.

K Rimu eto ochen' idot.";
        
        $expected =
                ['text'=>"Nad Rimom navisli tomnyye tuchi.

K Rimu eto ochen' idot.",
                 'title' => null,
                 'creation_date' => null];
        $text = new Text();
        $text_result = $text->parseWikitext( $wikitext );
        $this->assertEquals($expected, $text_result);
    }

    public function testParseWikitext_withMagicWords()
    {
        $wikitext = "{{Отексте
|КАЧЕСТВО=75%
| НАЗВАНИЕ=Я хочу рассказать вам
| АВТОР =[[Михаил Юрьевич Лермонтов]], (1814 -1841)
}}__NOTOC____NOEDITSECTION__


== <«Я ХОЧУ РАССКАЗАТЬ ВАМ»> ==

Я хочу рассказать вам историю женщины, которую вы все видали и которую никто из вас не знал. Вы ее встречали ежедневно на бале, в театре, на гулянье, у нее в кабинете. Теперь она уже сошла со сцены большого света; ей 30 лет, и она схоронила себя в деревне; но когда ей было только двадцать, весь Петербург шумно занимался ею в продолжение целой зимы. Об этом совершенно забыли, и слава богу! потому что иначе я бы не мог печатать своей повести. В обществе про нее было в то время много разногласных толков. Старушки говорили об ней, что она прехитрая и прелукавая, приятельницы - что она преглупенькая, соперницы - что она предобрая, молодые женщины - что она кокетка, а раздушенные старики значительно улыбались при ее имени и ничего не говорили. Еще прибавлю странность. Иные жалели, что такой правильной и свежей красоте недостает физиономии, тогда как другие утверждали, что хотя она вовсе не хороша, но неизъяснимая прелесть выраженья в ее лице заменяет все прочие недостатки. Притом муж ее, пятидесятилетний мужчина, имел графский титул и сомнительно-огромное состоянье. Всего этого, кажется, довольно, чтобы доставить молодой женщине ту соблазнительную, мимолетную славу, за которой они все так жадно гоняются и за которую некоторые из них так дорого платят.


Категория:Проза Михаила Юрьевича Лермонтова
";
        
        $expected ="Я хочу рассказать вам историю женщины, которую вы все видали и которую никто из вас не знал. Вы ее встречали ежедневно на бале, в театре, на гулянье, у нее в кабинете. Теперь она уже сошла со сцены большого света; ей 30 лет, и она схоронила себя в деревне; но когда ей было только двадцать, весь Петербург шумно занимался ею в продолжение целой зимы. Об этом совершенно забыли, и слава богу! потому что иначе я бы не мог печатать своей повести. В обществе про нее было в то время много разногласных толков. Старушки говорили об ней, что она прехитрая и прелукавая, приятельницы - что она преглупенькая, соперницы - что она предобрая, молодые женщины - что она кокетка, а раздушенные старики значительно улыбались при ее имени и ничего не говорили. Еще прибавлю странность. Иные жалели, что такой правильной и свежей красоте недостает физиономии, тогда как другие утверждали, что хотя она вовсе не хороша, но неизъяснимая прелесть выраженья в ее лице заменяет все прочие недостатки. Притом муж ее, пятидесятилетний мужчина, имел графский титул и сомнительно-огромное состоянье. Всего этого, кажется, довольно, чтобы доставить молодой женщине ту соблазнительную, мимолетную славу, за которой они все так жадно гоняются и за которую некоторые из них так дорого платят.";
        $text = new Text();
        $text_result = $text->parseWikitext( $wikitext );
        $this->assertEquals($expected, $text_result['text']);
    }

    public function testParseWikitext_textWithIncludedPoetry()
    {
        $wikitext = "{{Otekste
| AVTOR = Aleksandr Sergeyevich Pushkin
| NAZVANIYe = Stantsionnyy smotritel'
| DATASOZDANIYA = 1830
}}
Loshadi byli davno gotovy, a mne vse ne khotelos' rasstat'sya s smotritelem i yego dochkoy. Nakonets ya s nimi prostilsya; otets pozhelal mne dobrogo puti, a doch' provodila do telegi. V senyakh ya ostanovilsya i prosil u ney pozvoleniya yeye potselovat'; Dunya soglasilas'… Mnogo mogu ya naschitat' potseluyev,

{{poemx1||
S tekh por, kak etim zanimayus',<ref>Stroka vydelena v tekste kak tsitata, no yeyo istochnik ne ustanovlen.</ref>
|}}
{{Noindent|no ni odin ne ostavil vo mne stol' dolgogo, stol' priyatnogo vospominaniya.}}

Proshlo neskol'ko let, i obstoyatel'stva priveli menya na tot samyy trakt, v te samyye mesta. YA vspomnil doch' starogo smotritelya i obradovalsya pri mysli, chto uvizhu yeye snova. No, podumal ya, staryy smotritel', mozhet byt', uzhe smenen; veroyatno Dunya uzhe zamuzhem. Mysl' o smerti togo ili drugogo takzhe mel'knula v ume moyem, i ya priblizhalsya k stantsii *** s pechal'nym predchuvstviyem.";
        
        $expected ="Loshadi byli davno gotovy, a mne vse ne khotelos' rasstat'sya s smotritelem i yego dochkoy. Nakonets ya s nimi prostilsya; otets pozhelal mne dobrogo puti, a doch' provodila do telegi. V senyakh ya ostanovilsya i prosil u ney pozvoleniya yeye potselovat'; Dunya soglasilas'… Mnogo mogu ya naschitat' potseluyev,

S tekh por, kak etim zanimayus',
no ni odin ne ostavil vo mne stol' dolgogo, stol' priyatnogo vospominaniya.

Proshlo neskol'ko let, i obstoyatel'stva priveli menya na tot samyy trakt, v te samyye mesta. YA vspomnil doch' starogo smotritelya i obradovalsya pri mysli, chto uvizhu yeye snova. No, podumal ya, staryy smotritel', mozhet byt', uzhe smenen; veroyatno Dunya uzhe zamuzhem. Mysl' o smerti togo ili drugogo takzhe mel'knula v ume moyem, i ya priblizhalsya k stantsii *** s pechal'nym predchuvstviyem.";
        $text = new Text();
        $text_result = $text->parseWikitext( $wikitext );
//print "___\n";

//print $text_result['text'];
//print "___\n";
        $this->assertEquals($expected, $text_result['text']);
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
    
    public function testsplitIntoSentences_PointWithinSentence()
    {
        $text = "1. Razreshit' NKO SSSR vo izmeneniye poryadka, ustanovlennogo Postanovleniyem GOKO ot 4 noyabrya 1944 g. № 6884s, napravit' dlya raboty na predpriyatiya ugol'noy promyshlennosti, chernoy metallurgii i na lesozagotovki Narkomlesa SSSR v rayony Kamskogo basseyna voyennosluzhashchikh Krasnoy Armii, osvobozhdennykh iz nemetskogo plena, proshedshikh predvaritel'nuyu registratsiyu; repatriiruyemykh sovetskikh grazhdan, priznannykh po sostoyaniyu zdorov'ya godnymi k voyennoy sluzhbe i podlezhashchikh po zakonu mobilizatsii v Krasnuyu Armiyu.";
        $expected = ["1. Razreshit' NKO SSSR vo izmeneniye poryadka, ustanovlennogo Postanovleniyem GOKO ot 4 noyabrya 1944 g. № 6884s, napravit' dlya raboty na predpriyatiya ugol'noy promyshlennosti, chernoy metallurgii i na lesozagotovki Narkomlesa SSSR v rayony Kamskogo basseyna voyennosluzhashchikh Krasnoy Armii, osvobozhdennykh iz nemetskogo plena, proshedshikh predvaritel'nuyu registratsiyu; repatriiruyemykh sovetskikh grazhdan, priznannykh po sostoyaniyu zdorov'ya godnymi k voyennoy sluzhbe i podlezhashchikh po zakonu mobilizatsii v Krasnuyu Armiyu."];
        $text_result = Text::splitIntoSentences($text);
        $this->assertEquals($expected, $text_result);
    }

/* V dueli uchastvovali g. Pushkin - разбивается на 2 предложения 
 *   public function testsplitIntoSentences_WithinAbbr()
    {
        $text = "Vopros zdes' ne v tom, skol'ko dney ili let vy uchite tot ili inoy yazyk, vopros v tom, chto vam real'no nuzhno obuchit' programmu ponimat' tekst. Konkretnyy yazyk programmirovaniya tut ne pri chom, eto vopros teorii. Vy ne mozhete po-logkomu, na osnove formal'nykh kriteriyev, otlichit' konets predlozheniya ot sokrashcheniya. Sravnite, naprimer: «V dueli uchastvovali g. Pushkin i g. Dantes» i «Moi stikhi — odno sploshnoye g. Pushkin by zastrelilsya, no ne stal chitat' takoye.";
        $expected = ["Vopros zdes' ne v tom, skol'ko dney ili let vy uchite tot ili inoy yazyk, vopros v tom, chto vam real'no nuzhno obuchit' programmu ponimat' tekst.", 
            "Konkretnyy yazyk programmirovaniya tut ne pri chom, eto vopros teorii.",
            "Vy ne mozhete po-logkomu, na osnove formal'nykh kriteriyev, otlichit' konets predlozheniya ot sokrashcheniya.", 
            "Sravnite, naprimer: «V dueli uchastvovali g. Pushkin i g. Dantes» i «Moi stikhi — odno sploshnoye g. ",
            "Pushkin by zastrelilsya, no ne stal chitat' takoye."];
        $text_result = Text::splitIntoSentences($text);
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
    
    public function testSplitIntoParagraphs_cyrillic()
    {
        $text = "Вновь подтверждая свое стремление к созданию системы коллективной безопасности в Европе, основанной на участии в ней всех европейских государств, независимо от их общественного и государственного строя, что позволило бы объединить их усилия в интересах обеспечения мира в Европе,

Учитывая вместе с тем положение, которое создалось в Европе в результате ратификации парижских соглашений, предусматривающих образование новой военной группировки в виде \"западноевропейского союза\" с участием ремилитаризуемой Западной Германии и с включением ее в Североатлантический блок, что усиливает опасность новой войны и создает угрозу национальной безопасности миролюбивых государств,";

        $expected = [
            "Вновь подтверждая свое стремление к созданию системы коллективной безопасности в Европе, основанной на участии в ней всех европейских государств, независимо от их общественного и государственного строя, что позволило бы объединить их усилия в интересах обеспечения мира в Европе,",

"Учитывая вместе с тем положение, которое создалось в Европе в результате ратификации парижских соглашений, предусматривающих образование новой военной группировки в виде \"западноевропейского союза\" с участием ремилитаризуемой Западной Германии и с включением ее в Североатлантический блок, что усиливает опасность новой войны и создает угрозу национальной безопасности миролюбивых государств,"];

        $text_result = Text::splitIntoParagraphs($text);
        $this->assertEquals($expected, $text_result);
    }
    
    
}
