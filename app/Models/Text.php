<?php

namespace Wcorpus\Models;

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
     * @param String $wikitext - wikified text
     * @return INT author ID
     */
    public static function parseWikitext($wikitext) {
        if (preg_match("/\{\{Poemx?\|([^\|])|(\<poem\>)*([^\|])(\<\/poem\>)*|([^\}])\}\}]+)/i",$wikitext,$regs)) {
            $title = trim($regs[1]);
            $text = trim($regs[3]);
            $creation_date = trim($regs[5]);
        } else {
            $text = preg_replace("/(\{\{[^\}]\}\})/","",$wikitext);
            $title = $creation_date = null;
        }
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
    
}
