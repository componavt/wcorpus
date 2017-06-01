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
            $pos = $paradigm[0]->getPartOfSpeech();
            $pos_obj = POS::firstOrCreate(['aot_name'=>$pos]);
            if (!$pos_obj->name) {
                $pos_obj->name = $pos;
                $pos_obj->save();
            }
            
            $animative = $name = NULL; 
            if ($paradigm[0]->hasGrammems('ОД')) {
                $animative = 1;
            } elseif ($paradigm[0]->hasGrammems('НО')) {
                $animative = 0;
            }

            if ($paradigm[0]->hasGrammems('ФАМ')) {
                $name = 'ФАМ';
            } elseif ($paradigm[0]->hasGrammems('ИМЯ')) {
                $name = 'ИМ';
            } elseif ($paradigm[0]->hasGrammems('ОТЧ')) {
                $name = 'ОТЧ';
            }
            if ($name) {
                $name_obj = Gram::firstOrCreate(['aot_name'=>$name]);
                if (!$name_obj->name) {
                    $name_obj->name = $name;
                    $name_obj->save();
                }
                $name = $name_obj->id;
            }

            $lemmas[] = ['lemma' => $paradigm[0]->getWord(),
                         'pos_id' => $pos_obj->id,
                         'animative' => $animative,
                         'name' => $name,
                         'dictionary' => $dictionary];
        }
        
        return $lemmas;
    }
}
