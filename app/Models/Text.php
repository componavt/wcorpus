<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

use Wcorpus\Wikiparser\TemplateExtractor;
use Wcorpus\Models\Author;
use Wcorpus\Models\Publication;

class Text extends Model
{
    protected $fillable = ['publication_id','text'];
    public $timestamps = false;
    
    // Text __belongs_to__ Author
    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    // Text __belongs_to__ Publication
    public function publication()
    {
        return $this->belongsTo(Publication::class);
    }

    // Text __has_many__ Sentences
    public function sentences()
    {
        return $this->hasMany(Sentence::class);
    }

    /** delete all linked Sentences
     */
    public function deleteSentences() {
        $this->sentence_total = NULL;
        $this->save();
        
        if ($this->sentences()) {
            foreach ($this->sentences as $sentence) {
                $sentence->deleteWordforms();
                $sentence->delete();
            }
        }
    }

    /** Get $text->wikitext and look for title, creation_date, text
     * fill properties and save object
     * 
     * @param Text $text object of text
     */
    public function parseData() {
        $text = $this;
print "<p>".$text->id;           
        $wikitext = TemplateExtractor::removeComments($text->wikitext); // remove comments

        $wikitext = TemplateExtractor::removeTale($text->wikitext); 

        $wikitext = TemplateExtractor::removeWikiLinks($wikitext); // remove wiki links

        $wikitext = TemplateExtractor::removeLangTemplates($wikitext); // remove lang templates

        $wikitext = TemplateExtractor::removeRefTags($wikitext); // remove tags <ref...>...</ref>

        $text->author_id = Author::searchAuthorID($wikitext); // extract author

        $text_info = self::parseWikitext($wikitext);
        $text->text = $text_info['text'];

        $text->publication_id = Publication::parseWikitext(
                                                $wikitext, 
                                                $text->author_id,
                                                $text_info['title'],
                                                $text_info['creation_date']
                );
        if ($text->publication && $text->publication->author_id) {
            $text->author_id = $text->publication->author_id;
        }
        $text->push();
    }
    
    /** Get $text->text and break it into paragraphs,
     * and paragraphs split into sentences
     */
    public function breakIntoSentences() {
        $text = $this;
        
        $text->sentence_total = 0;
                
        $paragraphs = Text::splitIntoParagraphs($text->text);
//dd($paragraphs);        
        
        foreach($paragraphs as $par) {
            $sentences = Text::splitIntoSentences($par);
            foreach ($sentences as $sen) {                
                // if any words (with 2 and more letters) exists in this sentence
//                if (preg_match("/(([[:alpha:]]+[-])*[[:alpha:]]+?)/u",$sen)) { 
                if (preg_match("/[А-Яа-я]{2,}/u",$sen)) { 
                
/*
 * id=26189
                print "<p>$sen</p>\n"; 
print "<p>".mb_strlen($sen)."</p>";
print 16^4;
*/
                    if (mb_strlen($sen)>35003) {
                        $sen = mb_substr($sen,0,35000).'...';
                    }

                    $sen_obj = Sentence::create([
                        'text_id' => $text->id,
                        'sentence' => $sen
                            ]);
                    $text->sentence_total +=1;
                }
            }
        }
        
        $text->save();
    }
        
    /** Parse wikitext and extract text of publication
     * 
     * If this text is poetry, parse such template
     * 
     * {{Poemx|1|2|3}},
     * где:
     * 
     * 1 — Заголовок стихотворного произведения. Если этот параметр пропущен, то в качестве заголовка выводятся три звёздочки.
     *     Если стоит &nbsp; - Без заголовка, без звёздочек (для частей стихотворения на нескольких страницах)
     * 2 — Тело стихотворного произведения.
     * 3 — Дата создания (или иной текст, который должен быть изображен с соответствующим форматированием ниже тела стихотворения).
     * 
     * {{poemx|[[Эпиграмма]]|
     * (ПОДРАЖАНИЕ ФРАНЦУЗСКОМУ)
     * 
     * Супругою твоей я так пленился,
     * Что если б три в удел достались мне,
     * Подобные во всем твоей жене,
     * То даром двух я б отдал сатане
     * Чтоб третью лишь принять он согласился.
     * |Апрель, 1814}}
     * 
     * Аналогичный {{Poem|1|2|3}}, только 2 заключен в <poem> и </poem>
     * 
     * In another cases remove all templates
     * 
     * @param String $wikitext - wikified text
     * @return Array
     */
    public static function parseWikitext($wikitext) {
        $text_info = ['text'=>$wikitext,
                      'title' => null,
                      'creation_date' => null
               ];
    
        if( !$wikitext ) {
            return $text_info;
        }
        
        $text_info['text'] = TemplateExtractor::parsePoetryLadder($wikitext);
        
        if (preg_match("/\{\{poem\-on\|/i",$text_info['text'])) {
            $text_info = TemplateExtractor::extractPoem_on($text_info);
        }
        
        while (preg_match("/\{\{(poemx?1?)\|/",$text_info['text'], $regs)) {
            $template_name = $regs[1];
            $splited_text = TemplateExtractor::divideByTemplate($text_info['text'],$template_name);
            
            $title = TemplateExtractor::getParameterValueWithoutNames($template_name, 1,  $splited_text[1]);
            $text = TemplateExtractor::getParameterValueWithoutNames($template_name, 2, $splited_text[1]); 
            $creation_date = TemplateExtractor::getParameterValueWithoutNames($template_name, 3, $splited_text[1]); 
            
            if ($title && !$text_info['title']) {
                $text_info['title'] =  $title;
            }
            if ($creation_date && !$text_info['creation_date']) {
                $text_info['creation_date'] =  $creation_date;
            }
            
            $text_info['text']  = $splited_text[0].$text.$splited_text[2];
        }
        
        if ($text_info['title']) {
            $text_info['title'] = TemplateExtractor::clearText($text_info['title']);
        }
        $text_info['text'] = TemplateExtractor::clearText($text_info['text']);
//print "___\n";
//print $text_info['text'];
//print "___\n";
        if ($text_info['creation_date']) {
            $text_info['creation_date'] = TemplateExtractor::clearDate($text_info['creation_date']);
        }
        return $text_info;
    }
    
    /**
     * Split a text into paragraphs
     *
     * @param $text String text 
     * @return Array collection of paragraphs
     */
    public static function splitIntoParagraphs($text): Array
    {
        $paragraphs = [];
        $text = trim($text);
        
        if (!$text) {
            return $paragraphs;
        }
/*        
        $text = str_replace(chr(13),'',$text);
        $paragraphs = explode("\n\n",$text);
 * 
 */
        
/*  
        if (preg_match_all("/(\n|^)([^\n]+)(?=\n|$)/s",$text,$regs, PREG_PATTERN_ORDER)) {
            foreach ($regs[2] as $reg) {
                   $reg = trim($reg);
                   if ($reg) {
                        $paragraphs[] = $reg;
                   }
            }
        } else {
            $paragraphs[] = $text;
        }
 * 
 */
        
/*
        $text = preg_replace("/\r\n/u","\n",$text);
        $text = preg_replace("/\r/u","\n",$text);
        $paragraphs = preg_split("/\n{2,}/su",$text);
*/
        $text = nl2br($text);
        $text = preg_replace("/\<br \/\>\s*/u","\n",$text);
        $paragraphs = explode("\n\n",$text);
            
//print_r($paragraphs);
        return $paragraphs;
    }    
    
    /**
     * Split a paragraph into sentences
     * Punctuation marks are discarded
     *
     * @param $text String text of paragraph
     * @return Array collection of sentences
     */
    public static function splitIntoSentences($text): Array
    {
        $sentences = [];
        $text = trim($text);

        if (!$text) {
            return $sentences;
        }
        
        $text = preg_replace("/\n/",' ',$text);
        
        if (preg_match_all("/((\d+\.\s*)*[А-ЯA-Z]((т.п.|т.д.|пр.|g.)|[^?!.\(]|\([^\)]*\))*[.?!])/u",$text,$regs, PREG_PATTERN_ORDER)) {
            $sentences = $regs[0];
        } else {
            $sentences[] = $text;
        }
        
/*
        $sen_count = 1;
        $word_count = 1;

        $end1 = ['.','?','!','…'];
        $end2 = ['.»','?»','!»','."','?"','!"','.”','?”','!”'];
        $pseudo_end = false;
        if (!in_array(mb_substr($text,-1,1),$end1) && !in_array(mb_substr($text,-1,2),$end2)) {
            $text .= '.';
            $pseudo_end = true;
        }

        if (preg_match_all("/(.+?)(\.|\?|!|\.»|\?»|!»|\.\"|\?\"|!\"|\.”|\?”|!”|…{1,})(\s|(<br(| \/)>\s*){1,}|$)/is", // :|
                           $text, $desc_out)) {
            for ($k=0; $k<sizeof($desc_out[1]); $k++) {
                $sentence = trim($desc_out[1][$k]);

                // <br> in in the beginning of the string is moved before the sentence
                if (preg_match("/^(<br(| \/)>)(.+)$/is",$sentence,$regs)) {
                    $sentence = trim($regs[3]);
                }

                $sentences[] = str_replace("<br \>\n",'',$sentence);
            }
        }
*/
        return $sentences;
    }    
        
}
