<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

use Wcorpus\Models\Lemma;
use Wcorpus\Models\Sentence;

class Bigram extends Model
{
    protected $connection = 'mysql';
    protected $fillable = ['author_id','lemma1','lemma2','count1','count12'];
    protected $table = 'bigram_author';
    
    public $timestamps = false;
    
    /**
     * 
     * @param INT $author_id
     * @param INT $lemma1
     * @param INT $lemma2
     */
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
    }
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
        
        $query = "SELECT count12/count1 as prob FROM bigram_author WHERE author_id='".$author_id."' ";
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
        
        $query = "SELECT count1, count12, count12/count1 as probability FROM bigram_author WHERE author_id='".$author_id."' ";
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
            return ['count12'=>$result[0]->count12, 'count1'=>$result[0]->count1, 'probability'=>$result[0]->probability];
        } else {
            $out;
        }
    }
}
