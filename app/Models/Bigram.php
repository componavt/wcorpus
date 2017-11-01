<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

use Wcorpus\Models\Lemma;
use Wcorpus\Models\Sentence;

class Bigram extends Model
{
    protected $connection = 'mysql';
    protected $fillable = ['author_id','text_id','sentence_id','lemma1','lemma2','count1','count12'];
/*    protected $fillable = ['author_id','lemma1','lemma2','count1','count12'];
    protected $table = 'bigram_author'; */
    
    public $timestamps = false;
    
    /**
     * 
     * @param INT $author_id
     * @param INT $text_id
     * @param INT $sentence_id
     * @param INT $lemma1
     * @param INT $lemma2
     */ 
    public static function createBigram(INT $author_id, $text_id, $sentence_id, $lemma1, $lemma2) {
        if (!$author_id || !$text_id || !$sentence_id) {
            return;
        }
        $bigram = self::create(['author_id'=>$author_id,
                                'text_id' => $text_id,
                                'sentence_id' => $sentence_id,
                                'lemma1' => $lemma1,
                                'lemma2' => $lemma2,
                               ]);
        $bigram->save();
    }
    
    /**
     * 
     * @param INT $author_id
     */ 
    public static function createAuthorBigrams(INT $author_id) {
        if (!$author_id) {
            return;
        }
        
        $is_exists_not_processed = true;

        while ($is_exists_not_processed) {
            $sentences = Sentence::where('bigram_processed',0)
                                  ->whereIn('text_id',function($query) use ($author_id){
                                        $query->select('id')
                                        ->from('texts')
                                        ->where('author_id', $author_id);
                                    })->take(10)->get();
            if (!sizeof($sentences)) {
                $is_exists_not_processed = false;
                continue;
            }      

            foreach($sentences as $sentence) {
print "<p>".$sentence->sentence;                    
                $wordforms = Wordform::leftJoin('sentence_wordform','sentence_wordform.wordform_id','=','wordforms.id')
                                     ->where('sentence_id',$sentence->id)
                                     ->orderBy('word_number')->get();
                $lemmas1[0] = new Lemma;
                foreach ($wordforms as $wordform) {
//print "<br>".$wordform->wordform. ", ".$wordform->word_number;                        
                    $lemmas2 = $wordform->lemmas;
                    foreach ($lemmas1 as $lemma1) {
                        foreach ($lemmas2 as $lemma2) {
//print "<br>".$lemma1->lemma." - ".$lemma2->lemma. ", ".$wordform->word_number;   
                            Bigram::createBigram($author_id, $sentence->text_id, $sentence->id, $lemma1->id, $lemma2->id);
                            //Bigram::updateBigram($author_id, $lemma1->id, $lemma2->id);
                        }
                    } 
                    $lemmas1 = $lemmas2;
                }
//print "<br>".$lemma1->lemma." - finish";   
                if ($lemma1->id) {
                    Bigram::createBigram($author_id, $sentence->text_id, $sentence->id, $lemma1->id, null);
//                        Bigram::updateBigram($author_id, $lemma1->id, null);
                }
                $sentence->bigram_processed=1;
                $sentence->save();
            }   
//$is_exists_not_processed = false;                
        }
            
    }
    
    /**
     * 
     * @param INT $author_id
     */ 
    public static function countAuthorLemmaFrequency(INT $author_id) {
        if (!$author_id) {
            return;
        }
        
        // lemma1 - begining of sentence
        $lemmas = self::whereNull('count1')
                      ->where('author_id', $author_id)
                      ->whereNull('lemma1')
                      ->first();
        
        if ($lemmas) {
            $count = Sentence::countByAuthor($author_id);
//dd($count);            
            DB::statement("update bigrams set count1=".(int)$count.
                        " where lemma1=NULL".
                        " and author_id=".(int)$author_id);
        }
        
        $is_exists_not_processed = true;

        while ($is_exists_not_processed) {
            $lemmas = self::whereNull('count1')
                             ->groupBy('author_id','lemma1')
                             ->where('author_id', $author_id)
                             ->whereNotNull('lemma1')
                             ->select(DB::raw('lemma1, count(*) as count'))
                             ->take(100)->get();
            if (!sizeof($lemmas)) {
                $is_exists_not_processed = false;
                continue;
            }      

            foreach($lemmas as $lemma) {
                DB::statement("update bigrams set count1=".(int)$lemma->count.
                        " where lemma1=".(int)$lemma->lemma1.
                        " and author_id=".(int)$author_id);
            }   
        }
    }
        
    /**
     * 
     * @param INT $author_id
     */ 
    public static function countAuthorBigramFrequency(INT $author_id) {
        if (!$author_id) {
            return;
        }
        
        $is_exists_not_processed = true;

        while ($is_exists_not_processed) {
            $lemmas = self::whereNull('count12')
                             ->groupBy('author_id','lemma1','lemma2')
                             ->where('author_id', $author_id)
                             ->select(DB::raw('lemma1, lemma2, count(*) as count'))
                             ->take(100)->get();
            if (!sizeof($lemmas)) {
                $is_exists_not_processed = false;
                continue;
            }      

            foreach($lemmas as $lemma) {
                $query = "update bigrams set count12=".(int)$lemma->count.
                        " where lemma1";
                if ($lemma->lemma1 === null) {
                    $query .= " is null";
                } else {
                    $query .= "=".(int)$lemma->lemma1;
                }
                $query .= " and lemma2";
                if ($lemma->lemma2 === null) {
                    $query .= " is null";
                } else {
                    $query .= "=".(int)$lemma->lemma2;
                }
                $query .= " and author_id=".(int)$author_id;
                
                DB::statement($query);
            }   
        }
    }
/**
     * 
     * @param INT $author_id
     * @param INT $lemma1
     * @param INT $lemma2
     */ /*
    public static function updateBigram(INT $author_id, $lemma1, $lemma2) {
        if (!$author_id) {
            return;
        }
        $bigram = self::where('author_id',$author_id)
                      ->where('lemma1',$lemma1)
                      ->where('lemma2',$lemma2)->first();
        if ($bigram) {
//print "<p>!!!";            
            $bigram->count12 = 1+$bigram->count12;
            $bigram->save();
        } else {
            if ($lemma1) {
                $bigram=Bigram::where('lemma1',$lemma1)->first();
                if ($bigram && $bigram->count1) {
                    $count1 = $bigram->count1;
                } else {
                    $count1 = Lemma::countByIDAndAuthor($lemma1,$author_id);
                }
            } else {
                $bigram=Bigram::whereNull('lemma1')->first();
                if ($bigram && $bigram->count1) {
                    $count1 = $bigram->count1;
                } else {
                    $count1 = Sentence::countByAuthor($author_id);
                }
            }
            $bigram = self::create(['author_id'=>$author_id,
                                    'lemma1' => $lemma1,
                                    'lemma2' => $lemma2,
                                    'count1' => $count1,
                                    'count12' => 1
                                   ]);
            $bigram->save();
        }       
    }*/
    /**
     * 
     * @param INT $author_id
     * @param INT $lemma1
     * @param INT $lemma2
     */
    public static function getProbability(INT $author_id, $lemma1, $lemma2) {
        if (!$author_id) {
            return 0;
        }
        
        $query = "SELECT count12/count1 as prob FROM bigrams WHERE author_id='".$author_id."' ";
        if (!$lemma1) {
            $query .= " AND lemma1 is null";
        } else {
            $query .= " AND lemma1 = ".(int)$lemma1;
        }
        if (!$lemma1) {
            $query .= " AND lemma2 is null";
        } else {
            $query .= " AND lemma2 = ".(int)$lemma2;
        }
        $result = DB::select(DB::raw($query));
        if ($result) {
            return $result[0]->prob;
        } else {
            return 0;
        }
    }
    /**
     * 
     * @param INT $author_id
     * @param INT $lemma1
     * @param INT $lemma2
     */
    public static function getCountsAndProbability(INT $author_id, $lemma1, $lemma2) {
        $out = ['count12'=>'','count1'=>'','probability'=>0];
        if (!$author_id) {
            $out;
        }
        
        $query = "SELECT count1, count12, count12/count1 as probability FROM bigrams WHERE author_id='".$author_id."' ";
        if (!$lemma1) {
            $query .= " AND lemma1 is null";
        } else {
            $query .= " AND lemma1 = ".(int)$lemma1;
        }
        if (!$lemma1) {
            $query .= " AND lemma2 is null";
        } else {
            $query .= " AND lemma2 = ".(int)$lemma2;
        }
//print "<p>$query";        
        $result = DB::select(DB::raw($query));
        if ($result) {
            return ['count12'=>$result[0]->count12, 'count1'=>$result[0]->count1, 'probability'=>$result[0]->probability];
        } else {
            $out;
        }
    }
}
