<?php

namespace Wcorpus\Models;

use Wcorpus\Wikiparser\TemplateExtractor;
use Illuminate\Database\Eloquent\Model;

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
        $title = 
        $text =
        $creation_date = null;
    
        if( !$wikitext ) {
            return ['text'=>$text, 'title' => $title, 'creation_date' => $creation_date];
        }
        
        // extracts a text of second parameter from the template {{Poemx|1|2|3}}
        $template_name = "Poemx";
        $parameter_number = 2;
        $text = TemplateExtractor::getParameterValueWithoutNames($template_name, $parameter_number, $wikitext);
        
        /*
        if (preg_match("/\{\{Poemx?\|([^\|]*)\|(\<poem\>)*([^\|]+)(\<\/poem\>)*\|([^\}]*)\}\}/i",$wikitext,$regs)) {
            $title = trim($regs[1]);
            $text = trim($regs[3]);
            $creation_date = trim($regs[5]);
            if (mb_strlen($creation_date)>50) {
                $creation_date = mb_substr($creation_date,0,50);
            }
        } else {
            $text = preg_replace("/(\{\{[^\}]\}\})/","",$wikitext);
        }*/
        
        return ['text'=>$text,
                'title' => $title,
                'creation_date' => $creation_date
               ];
    }
    /** Takes data from search form (title, language) and 
     * returns string for url such_as 
     * title=$title&lang_id=$lang_id
     * IF value is empty, the pair 'argument-value' is ignored
     * 
     * @param Array $url_args - array of pairs 'argument-value', f.e. ['title'=>'...', lang_id=>1]
     * @return String f.e. 'pos_id=11&lang_id=1'
     */
    public static function searchValuesByURL(Array $url_args=NULL) : String
    {
        $url = '';
        if (isset($url_args) && sizeof($url_args)) {
            $tmp=[];
            foreach ($url_args as $a=>$v) {
                if ($v!='' && !($a=='page' && $v==1) && !($a=='limit_num' && $v==10)) {
                    $tmp[] = "$a=$v";
                }
            }
            if (sizeof ($tmp)) {
                $url .= "?".implode('&',$tmp);
            }
        }
        
        return $url;
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
     * @param $text String text 
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
    
    /**
     * Split a sentence into words without punctuation marks
     *
     * @param $text String text 
     * @return Array collection of words
     */
    public static function splitIntoWords($text): Array
    {
        $words = [];
        $text = trim($text);

        if (!$text) {
            return $words;
        }
        
        if (preg_match_all("/(([[:alpha:]]+['-])*[[:alpha:]]+'?)/u",$text,$regs, PREG_PATTERN_ORDER)) {
            $words = $regs[0];
        } else {
            $words[] = $text;
        }

        return $words;
    }
    
}
