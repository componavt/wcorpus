<?php

namespace Wcorpus\Http\Controllers;

use Illuminate\Http\Request;
use DB;

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
                    'limit_num'       => (int)$request->input('limit_num'),
                    'page'            => (int)$request->input('page'),
                    'search_author'  => $request->input('search_author'),
                    'search_author2'  => $request->input('search_author2'),
                    'order_by'      => $request->input('order_by'),
                    'min_count1'      => $request->input('min_count1'),
                    'min_count12'      => $request->input('min_count12'),
                    'max_count1'      => $request->input('max_count1'),
                    'max_count12'      => $request->input('max_count12'),
//                    'conversely'    =>(int)$request->input('conversely'),
                ];
        
        if (!$this->url_args['page']) {
            $this->url_args['page'] = 1;
        }
        
        if ($this->url_args['limit_num']<=0) {
            $this->url_args['limit_num'] = 50;
        } elseif ($this->url_args['limit_num']>1000) {
            $this->url_args['limit_num'] = 1000;
        }   
        
        if (!$this->url_args['order_by']) {
            $this->url_args['order_by'] = 'probability';
        }
        
        if (!$this->url_args['min_count1']) {
            $this->url_args['min_count1'] = 10;
        }
        
        if (!$this->url_args['min_count12']) {
            $this->url_args['min_count12'] = 10;
        }
//dd($this->url_args);        
        $this->args_by_get = Wcorpus::searchValuesByURL($this->url_args);
    }

    /**
     * View bigrams for authors $search_author and $search_author2.
     *
     * select lemma1, lemma2, count1, count12, count12/count1 as probability from `bigrams` where `author_id` = 62 and `count1` > 10 and `count12` > 10 group by `lemma1`, `lemma2`, `count1`, `count12` order by `count12` desc
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
//dd($this->url_args);
            
        $query = "SELECT DISTINCT author_id FROM bigrams";
        $authors = DB::select(DB::raw($query));
        $author_values= [];
        $bigrams = null;
        
        foreach ($authors as $author) {
            $author_values[$author->author_id] = Author::getNameByID($author->author_id);
        }

        if ($this->url_args['search_author'] && $this->url_args['search_author2']) {
            $bigrams = Bigram::where('author_id',$this->url_args['search_author'])
                     -> select(DB::raw('lemma1, lemma2, count1, count12, count12/count1 as probability'))
                     -> where('count1', '>=', (int)$this->url_args['min_count1'])
                     -> where('count12', '>=', (int)$this->url_args['min_count12'])
//                     -> take(10)
                     -> groupBy('lemma1','lemma2','count1','count12')
                     -> orderBy($this->url_args['order_by'], 'desc');
//dd($bigrams->toSql());            
            $bigrams = $bigrams -> get();
                    //paginate($this->url_args['limit_num']);         
        }
        
        return view('bigram.index')
              ->with(['author_values' => $author_values,
                      'bigrams' => $bigrams,
//                      'bigrams' => null,
                      'args_by_get'   => $this->args_by_get,
                      'url_args'      => $this->url_args,
                      ]
                );
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
            
            Bigram::createAuthorBigrams($author_id);
            
            Bigram::countAuthorLemmaFrequency($author_id);
            
            Bigram::countAuthorBigramFrequency($author_id);
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
