<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    protected $fillable = ['author_id','title','creation_date'];
    public $timestamps = false;

    // Publication __belongs_to__ Author
    public function author()
    {
        return $this->belongsTo(Author::class);
    }
    // Publication __has_many__ Texts
    public function texts()
    {
        return $this->hasMany(Text::class);
    }

    /** Parse wikitext and extract information about publication
     * 
     * {{Отексте
     * ...
     * | НАЗВАНИЕ              = …И будет, когда продлятся дни, от века те же…
     * |...
     * | ДАТАПУБЛИКАЦИИ        = 
     * |...
     * }}
     * 
     * OR
     * 
     * {{Отексте
     * ...
     * | НАЗВАНИЕ              = «[[…И Данте просветлённые напевы (Львова)|…И Данте просветлённые напевы]]…»
     * |...
     * | ДАТАСОЗДАНИЯ          =
     * | ДАТАПУБЛИКАЦИИ        = 1913
     * |...
     * }}
     * 
     * OR
     * 
     * {{Отексте
     * ...
     * | НАЗВАНИЕ=?
     * | ПОДЗАГОЛОВОК=«Пусть для ваших открытых сердец…»
     * |...
     * }}
     * 
     * @param String $wikitext - wikified text
     * @param Int $author_id - author ID
     * @return INT author ID
     */
    public static function parseWikitext($wikitext, $author_id, $text_title, $text_date) {
        $title = '';
        $creation_date='';
        
        if (!$wikitext) {
            return null;
        }
        
        if (preg_match("/\{\{О\s?тексте[^\}]+НАЗВАНИЕ\s*=\s*\[*([^\|\]\}]+)/",$wikitext,$regs)) {
            $title = trim($regs[1]);
            
            if (preg_match("/^([^\[]*)\[\[([^\|\]]+\|?[^\]]*\]\](.*)$)/",$title,$regs1)) {
                $title = $regs1[1].$regs1[2].$regs1[3];                
            }
            
        }
        
        // тут нужно извлечь дату
        // ...
        
        if (!$title && $text_title) {
            $title = $text_title;
        }
        if (!$creation_date && $text_date) {
            $creation_date = $text_date;
        }
        
        $publication = self::firstOrNew(['title'=>$title,'author_id'=>$author_id]);
        $publication->creation_date = $creation_date;
        $publication->save();
        
        print "<br>".$title;
        return $publication->id;
    }

}
