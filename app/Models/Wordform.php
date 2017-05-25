<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

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
}
