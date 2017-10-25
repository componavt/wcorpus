<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

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
            $bigram->count12 += 1;
        } else {
            if ($lemma1) {
                $count1 = Lemma::countByIDAndAuthor($lemma1,$author_id);
            } else {
                $count1 = Sentence::countByAuthor($author_id);
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
}
