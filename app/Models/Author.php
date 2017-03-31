<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
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

}
