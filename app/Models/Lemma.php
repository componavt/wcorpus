<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

use cijic\phpMorphy\Morphy;

class Lemma extends Model
{
    public static function lemmatize($word)
    {
        if (!$word) {
            return '';
        }
        
        $morphy = new Morphy('ru');
        $lemma = $morphy->getPseudoRoot($word);
        
        return $lemma;
    }
}
