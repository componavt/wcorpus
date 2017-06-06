<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

use Wcorpus\Wcorpus;
use Wcorpus\Models\Piwidict\LangPOS;

class Lemma extends Model
{
    protected $connection = 'mysql';
    protected $fillable = ['lemma','pos_id','dictionary','freq','animative','name_id'];
    public $timestamps = false;

    // Lemma __belongs_to__ PartOfSpeech
    public function pos()
    {
        return $this->belongsTo(POS::class,'pos_id');
    }
    
    // Lemma __belongs_to__ Grams
    public function named()
    {
        return $this->belongsTo(Gram::class,'name_id');
    }
    
    public function animative_name()
    {
        if ($this->animative === NULL) {
            return NULL;
        }
        elseif ($this->animative === 1) {
            return 'animate';
        }
        return 'inanimate';
    }
    
    // Lemmas __has_many__ Wordforms
    public function wordforms(){
        $builder = $this->belongsToMany(Wordform::class,'lemma_wordform');
//        $builder = $builder -> orderBy('lemma');
        return $builder;
    }
    
    // Lemmas __has_many__ LangPOSes
    public function lang_poses(){
        $builder = $this->belongsToMany(LangPOS::class,'wcorpus.lang_pos_lemma','lemma_id','lang_pos_id');
//        $builder = $builder -> orderBy('lemma');
        return $builder;
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
