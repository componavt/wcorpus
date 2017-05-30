<?php

namespace Wcorpus\Http\Controllers;

use Illuminate\Http\Request;

use Wcorpus\Models\Sentence;
use Wcorpus\Models\Text;
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
                    'search_text'  => (int)$request->input('search_text'),
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

        if ($this->url_args['search_text']) {
            $sentences = $sentences->where('text_id',$this->url_args['search_text']);
        } 
        
        $numAll = $sentences->get()->count();

        $sentences = $sentences
                ->paginate($this->url_args['limit_num']);         
        
        
            return view('sentence.index')
                  ->with(array(
                               'numAll'        => $numAll,
                               'sentences'     => $sentences,
                               'args_by_get'   => $this->args_by_get,
                               'url_args'      => $this->url_args,
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

        if ($sentence->wordforms()->count()) {
            $sentence->wordforms()->detach();
        }
        
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
    }
    
}
