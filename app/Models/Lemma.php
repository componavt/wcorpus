<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

use Wcorpus\Wcorpus;

class Lemma extends Model
{
    protected $fillable = ['lemma','pos_id','dictionary','freq','animative','name_id'];

    // Lemma __belongs_to__ PartOfSpeech
    // $pos_name = PartOfSpeech::find(9)->name_ru;
    public function pos()
    {
        return $this->belongsTo(POS::class,'pos_id');
    }
    
    public static function lemmatize($word)
    {
        if (!$word) {
            return '';
        }
            
        $morphy = Wcorpus::getMorphy();
        $word = mb_strtoupper($word);
        $lemma = $morphy->lemmatize($word);
            
        // $lemma = "some text";
            //$lemma=Morphy::getPseudoRoot($word);
            
        return $lemma;
    }
}
