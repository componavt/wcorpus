<?php

namespace Wcorpus\Models\Piwidict;

use Illuminate\Database\Eloquent\Model;

class PartOfSpeech extends Model
{
    protected $connection = 'ru_wikt';
    protected $table = 'part_of_speech';
    
    /**
     * 
     * @param String $name
     * @return INT ID of PartOfSpeech Object
     */
    public static function getIDByName(String $name) {
        $pos = self::where('name',$name)->first();
        if ($pos) {
            return $pos->id;
        } else {
            return NULL;
        }
    }
    
}
