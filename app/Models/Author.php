<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $fillable = ['name'];
    public $timestamps = false;

    // Author __has_many__ Texts
    public function texts()
    {
        return $this->hasMany(Text::class);
    }

    // Author __has_many__ Publications
    public function publications()
    {
        return $this->hasMany(Publication::class);
    }

    /** Gets list of authors
     * 
     * @return Array [1=>'Alexander Sergeevich Pushkin',..]
     */
    public static function getList($without=[])
    {     
        $authors = self::orderBy('name')->get();
        
        $list = array();
        foreach ($authors as $row) {
            if (!in_array($row->id, $without)) {
                $list[$row->id] = $row->name;
            }
        }
        
        return $list;         
    }
        
    /** Gets list of authors
     * 
     * @return Array [1=>'Alexander Sergeevich Pushkin', ..]
     */
    public static function getListWithQuantity($method_name)
    {     
        $authors = self::orderBy('name')->get();
        
        $list = array();
        foreach ($authors as $row) {
            $count=$row->$method_name()->count();
            $name = $row->name;
            if ($count) {
                $name .= " ($count)";
            }
            $list[$row->id] = $name;
        }
        
        return $list;         
    }

    /** Parse wikitext and extract author name
     * 
     * {{Отексте
     * ...
     * |АВТОР= [[Ганс Христиан Андерсен|Гансъ Христіанъ Андерсенъ]] (1805—1875)
     * |...
     * }}
     * 
     * OR
     * 
     * {{Отексте
     * ...
     * |АВТОР = [[Борис Степанович Житков]]
     * |...
     * }}
     * 
     * OR
     * 
     * {{Отексте
     * ...
     * | АВТОР  = Влас Михайлович Дорошевич
     * |...
     * }}
     * 
     *  @param $wikitext - wikified text
     *  @return String author name
     */
    public static function searchAuthorName($wikitext) {
        if (!$wikitext) {
            return null;
        }
        if (preg_match("/\{\{О\s?тексте[^\}]+АВТОР\s*=\s*\[*([^\(\|\]\}]+)/",$wikitext,$regs)) {
            $name=trim($regs[1]);
            //print "<br>".$name;
            
            return $name;
        } else {
            return null;
        }
        
    }
    
    /** Parse wikitext and extract information about author
     * 
     *  @param $wikitext - wikified text
     *  @return INT author ID
     */
    public static function searchAuthorID($wikitext) {
        if (!$wikitext) {
            return null;
        }
        
        $author_name = self::searchAuthorName($wikitext);
        
        if (!$author_name) {
            return null;
        }
//        print "<br>".$author_name;
        $author = self::firstOrCreate(['name'=>$author_name]);
        return $author->id;        
    }
}
