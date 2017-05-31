<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

use Wcorpus\Wcorpus;

class Wordform extends Model
{
    protected $fillable = ['wordform'];
    public $timestamps = false;
    
    // Wordforms __has_many__ Lemmas
    public function lemmas(){
        $builder = $this->belongsToMany(Lemma::class,'lemma_wordform');
//        $builder = $builder -> orderBy('lemma');
        return $builder;
    }
    
    // Wordforms __has_many__ Sentences
    public function sentences(){
        $builder = $this->belongsToMany(Sentence::class,'sentence_wordform');
        return $builder;
    }
    
    public function lemmatize()
    {
        $word = $this->wordform;
        if (!$word) {
            return '';
        }
         
        $morphy=Wcorpus::getMorphy();
        
        $word = mb_strtoupper($word);
        $lemmas = [];
        
        $collection = $morphy->findWord($word);
//print_r($collection);

        $dictionary = (int)!($morphy->isLastPredicted());
//        $lemma = $morphy->lemmatize($word);
            
        foreach($collection as $paradigm) {
            $lemmas[] = ['lemma' => $paradigm[0]->getWord(),
                         'pos' => $paradigm[0]->getPartOfSpeech(),
                         'dictionary' => $dictionary];
        }
        
        return $lemmas;
    }
}
