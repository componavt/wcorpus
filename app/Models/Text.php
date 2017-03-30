<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

class Text extends Model
{
    protected $fillable = ['publication_id','text'];
    
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
