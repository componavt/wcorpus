<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

use Wcorpus\Wcorpus;
use Wcorpus\Models\Piwidict\LangPOS;
//use Wcorpus\Models\LemmaMatrix;

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
    
    /** delete all records from lemma_matrix where lemma1 or lemma2 is $this->id
     */
    public function deleteFromMatrix() {
        DB::statement('DELETE FROM lemma_matrix WHERE lemma1='.$this->id.' OR lemma2='.$this->id);
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
    
    /**
     * check if this lemma has one of the basic parts of speech 
     * (noun, adjective, verb, adverb)
     * @return Boolean true -  if the part of speech of this lemma is basic
     */
    public function hasBasicPOS() {
        $basic_POS = [1,2,8,9];
        
        return in_array($this->pos_id, $basic_POS);
    }
}
