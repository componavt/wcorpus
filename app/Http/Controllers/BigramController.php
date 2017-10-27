<?php

namespace Wcorpus\Http\Controllers;

use Illuminate\Http\Request;

use Wcorpus\Models\Author;
use Wcorpus\Models\Bigram;
use Wcorpus\Models\Lemma;
use Wcorpus\Models\Sentence;
use Wcorpus\Models\Wordform;
use Wcorpus\Wcorpus;

class BigramController extends Controller
{
    public $url_args=[];
    public $args_by_get='';
    
     /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->url_args = [
//                    'limit_num'       => (int)$request->input('limit_num'),
//                    'page'            => (int)$request->input('page'),
//                    'search_wordform'  => (int)$request->input('search_sentence'),
                    'search_author'  => $request->input('search_author'),
//                    'order_by'      => $request->input('order_by'),
                ];
 /*       
        if (!$this->url_args['page']) {
            $this->url_args['page'] = 1;
        }
        
        if ($this->url_args['limit_num']<=0) {
            $this->url_args['limit_num'] = 10;
        } elseif ($this->url_args['limit_num']>1000) {
            $this->url_args['limit_num'] = 1000;
        }   
*/        
        $this->args_by_get = Wcorpus::searchValuesByURL($this->url_args);
    }

    /**
     * Create bigrams for author $search_author.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $author_values = [];
        if ($this->url_args['search_author']) {
            $author_id = $this->url_args['search_author'];
            $author_values[$author_id] = Author::getNameByID($author_id);
            
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
                                         -> orderBy('word_number')
                                         ->get();
                    $lemmas1[0] = new Lemma;
                    foreach ($wordforms as $wordform) {
//print "<br>".$wordform->wordform. ", ".$wordform->word_number;                        
                        $lemmas2 = $wordform->lemmas;
                        foreach ($lemmas1 as $lemma1) {
                            foreach ($lemmas2 as $lemma2) {
//print "<br>".$lemma1->lemma." - ".$lemma2->lemma. ", ".$wordform->word_number;   
                                Bigram::updateBigram($author_id, $lemma1->id, $lemma2->id);
                            }
                        } 
                        $lemmas1 = $lemmas2;
                    }
//print "<br>".$lemma1->lemma." - finish";   
                    Bigram::updateBigram($author_id, $lemma1->id, null);
                    $sentence->bigram_processed=1;
                    $sentence->save();
                }   
//$is_exists_not_processed = false;                
            }
        }      
        
        return view('bigram.create')
              ->with(['author_values' => $author_values,

//                               'matrix'        => $matrix,
//                              'args_by_get'   => $this->args_by_get,
                      'url_args'      => $this->url_args,
                      ]
                );
    }
}
