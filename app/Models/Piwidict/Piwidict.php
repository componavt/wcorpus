<?php

namespace Wcorpus\Models\Piwidict;

use Illuminate\Database\Eloquent\Model;

class Piwidict extends Model
{
    // Russian language
    private static $lang_id=804; 
    
    /**
     * 
     * @return Int Language ID by $lang_code
     */
    public static function lang() {
        return self::$lang_id;
    }
    
}
