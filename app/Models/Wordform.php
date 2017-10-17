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
    
    /** delete all linked Lemmas
     */
    public function deleteLemmas() {
        $this->lemma_total = NULL;
        $this->save();
        
        if ($this->lemmas()->count()) {
            foreach ($this->lemmas as $lemma) {
                $this->lemmas()->detach($lemma->id);
                if (!$lemma->wordforms()->wherePivot('wordform_id','<>',$this->id)->count()) { // this lemma links with only this wordform
                    $lemma->deleteFromMatrix();
                    $lemma->delete();
                }
            }
        }
    }

    // Wordforms __has_many__ Sentences
    public function sentences(){
        $builder = $this->belongsToMany(Sentence::class,'sentence_wordform')
                ->withPivot('word_number');
        return $builder;
    }
    
    /**
     * If the wordform has just been created, it is lemmatized
     * Add links with sentence
     * 
     * @param Integer $sentence_id
     * @param Integer $word_number - sequence number in the sentence with ID=$sentence_id
     */
    public function linkWithSentence(INT $sentence_id,INT $word_number){
        if ($this->lemma_total == null) { // только что создана
            $this->update_lemmas();
        }

        if ($this->lemma_total > 0) { // создаем связи с предложениями
            // словоформы без лемм с предложениями не связываются
            if ($this->lemma_total == 1) { // && $this-> lemmas() && $this-> lemmas() -> first()
                $lemma_found = 1;
                $lemma_id = $this-> lemmas() -> first() -> id;
            } else {
                $lemma_found = 
                $lemma_id = NULL;        
            }
            $this->sentences()->attach($sentence_id,
                    ['word_number' => $word_number,
                     'lemma_found' => $lemma_found,
                     'lemma_id' => $lemma_id  
                    ]); 

        }
    }
    
    /**
     * @param $only_with_basic_POS - only basic parts of speech is included 
     *                              (noun, adjective, verb, adverb) 
     * @return Array - lemmas ID
     */
    public function getLemmaIDs($only_with_basic_POS=1){
        $lemmas =[];
        foreach ($this->lemmas as $lemma) {
            if (!$only_with_basic_POS || $lemma -> hasBasicPOS()) {
                $lemmas[] = $lemma->id;
            }
        }
        return $lemmas;
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
//dd($collection);
//dd($morphy->isLastPredicted());
        $dictionary = (int)!($morphy->isLastPredicted());
//        $lemma = $morphy->lemmatize($word);

        if (!$collection) {
            return $lemmas;
        }
        
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
                         'name_id' => $name,
                         'dictionary' => $dictionary];
        }
        
        return $lemmas;
    }
    
    public function update_lemmas()
    {
        if ($this->lemmas()->count()) {
            $this->lemmas()->detach();
            $this->lemma_total = 0;
            $this->save();
        }
        
        $lemmas = $this->lemmatize();
        
        foreach ($lemmas as $lemma) {
print "<br>".$lemma['lemma']." (dictionary:".$lemma['dictionary'].", pos_id:".$lemma['pos_id'].", animative:".$lemma['animative'].", named: ". $lemma['name_id']. ")\n";            
            $lemma_obj = Lemma::firstOrCreate($lemma);
            $this->lemmas()->attach($lemma_obj);
        }
        $this->lemma_total = $this->lemmas()->count();
        $this->push();
    }
}
