<?php

namespace Wcorpus\Http\Controllers;

use Illuminate\Http\Request;

use Wcorpus\Models\Bigram;
use Wcorpus\Models\Sentence;
use Wcorpus\Models\Text;
use Wcorpus\Models\Wordform;
use Wcorpus\Wcorpus;

class SentenceController extends Controller
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
        // permission= dict.edit, redirect failed users to /dict/lemma/, authorized actions list:
        $this->middleware('auth', 
                          ['only' => ['create','store','edit','update','destroy',
                                      'breakSentence','breakAllSentences'
                                     ]]);
        
        $this->url_args = [
                    'limit_num'       => (int)$request->input('limit_num'),
                    'page'            => (int)$request->input('page'),
                    'bigram_lemma1'  => (int)$request->input('bigram_lemma1'),
                    'bigram_lemma2'  => (int)$request->input('bigram_lemma2'),
                    'search_author'  => (int)$request->input('search_author'),
                    'search_text'  => (int)$request->input('search_text'),
                    'search_wordform'  => (int)$request->input('search_wordform'),
                ];
        
        if (!$this->url_args['page']) {
            $this->url_args['page'] = 1;
        }
/*        
        if (!$this->url_args['search_id']) {
            $this->url_args['search_id'] = NULL;
        }
*/        
        if ($this->url_args['limit_num']<=0) {
            $this->url_args['limit_num'] = 10;
        } elseif ($this->url_args['limit_num']>1000) {
            $this->url_args['limit_num'] = 1000;
        }   
        
        $this->args_by_get = Wcorpus::searchValuesByURL($this->url_args);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sentences = Sentence::
                select('id')->
                orderBy('text_id');
                //->orderBy('id');

        $text_values = [];
        
        if ($this->url_args['search_text']) {
            $sentences = $sentences->where('text_id',$this->url_args['search_text']);
            $text_values[$this->url_args['search_text']] = Text::getTitleByID($this->url_args['search_text']);
        } 
        
        if ($this->url_args['search_author'] && ($this->url_args['bigram_lemma1'] || $this->url_args['bigram_lemma2'])) {
            $author_id = $this->url_args['search_author'];
            $lemma1 = $this->url_args['bigram_lemma1'];
            $lemma2 = $this->url_args['bigram_lemma2'];
            
            $sentences = $sentences->whereIn('id',function($query) use ($author_id, $lemma1, $lemma2){
                                $query->select('sentence_id')
                                ->from('bigrams')
                                ->where('author_id', $author_id);
                                if ($lemma1) {
                                    $query->where('lemma1',$lemma1);
                                } else {
                                    $query->whereNull('lemma1');
                                }
                                if ($lemma2) {
                                    $query->where('lemma2',$lemma2);
                                } else {
                                    $query->whereNull('lemma2');
                                }
                            });
        } 
        
        if ($this->url_args['search_wordform']) {
            $wordform_id = $this->url_args['search_wordform'];
            $sentences = $sentences->whereIn('id',function($query) use ($wordform_id){
                                $query->select('sentence_id')
                                ->from('sentence_wordform')
                                ->where('wordform_id', $wordform_id);
                            });
            $wordform = Wordform::find($wordform_id)->wordform;
        } else {
            $wordform = '';
        }
        
        $numAll = $sentences->get()->count();

        $sentences = $sentences
                ->paginate($this->url_args['limit_num']);         
        
        
            return view('sentence.index')
                  ->with(array(
                               'numAll'          => $numAll,
                               'sentences'       => $sentences,
                               'text_values'     => $text_values,
                               'wordform'        => $wordform,
                               'bigram_lemma1'   => $this->url_args['bigram_lemma1'],
                               'bigram_lemma2'   => $this->url_args['bigram_lemma2'],
                               'args_by_get'     => $this->args_by_get,
                               'url_args'        => $this->url_args,
                              )
                        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    /**
     * Break a sentence into words
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function breakSentence($id)
    {
        $sentence=Sentence::find($id);                
        
        $sentence->deleteWordforms();
        
        $sentence->breakIntoWords();
    }
    
    /**
     * Break all sentences into words
     *
     * @return \Illuminate\Http\Response
     */
    public function breakAllSentences()
    {
        // stop when there is no sentences with wordform_total=NULL
/*        
        $is_exist_not_broken_sentence = 1;
        
        while ($is_exist_not_broken_sentence) {
            $sentences=Sentence::
                    whereNull('wordform_total')
                    //->orderBy('text_id')
                    ->take(100)
                    ->get();
//dd($sentences);            
            if ($sentences->count()) {
                foreach ($sentences as $sentence) {
print "<p>".$sentence->id."</p>\n";                    
                    $sentence->breakIntoWords();
                }
            } else {
                $is_exist_not_broken_text = 0;
            }
        }
*/        
        foreach ([62,298,423] as $author_id) {
            Bigram::createAuthorBigrams($author_id);
            
            Bigram::countAuthorLemmaFrequency($author_id);
            
            Bigram::countAuthorBigramFrequency($author_id);
        }
    }
    
}
