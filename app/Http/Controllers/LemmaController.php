<?php

namespace Wcorpus\Http\Controllers;

use Illuminate\Http\Request;

use Wcorpus\Models\Lemma;
use Wcorpus\Wcorpus;
use Wcorpus\Models\Piwidict\LangPOS;

class LemmaController extends Controller
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
                                      //'breakSentence','breakAllSentences'
                                     ]]);
        
        $this->url_args = [
                    'limit_num'       => (int)$request->input('limit_num'),
                    'page'            => (int)$request->input('page'),
                    'search_wordform'  => (int)$request->input('search_sentence'),
                    'search_lemma'  => $request->input('search_wordform'),
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
        $lemmas = Lemma::
                select('id')->
                orderBy('lemma');
                //->orderBy('id');

        if ($this->url_args['search_wordform']) {
            $wordform_id = $this->url_args['search_wordform'];
        
            $lemmas = $wordforms->whereIn('id',function($query) use ($wordform_id){
                                $query->select('wordform_id')
                                ->from('lemma_wordform')
                                ->where('wordform_id', $wordform_id);
                            });                    
        } 
        
        if ($this->url_args['search_lemma']) {
            $lemmas = $lemmas->where('lemma','like',$this->url_args['search_lemma']);
        } 
        
        $numAll = $lemmas->get()->count();


        $lemmas = $lemmas
                ->paginate($this->url_args['limit_num']);         
        
        
            return view('lemma.index')
                  ->with(array(
                               'numAll'        => $numAll,
                               'lemmas'        => $lemmas,
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
        $lemma = Lemma::find($id);
       
        if (!$lemma) {
            return Redirect::to('/lemma/')
                           ->withErrors("Lemma ID=$id is not found");            
        }
        
        $grams = [];
        if ($lemma->animative_name) {
            $grams[]= $lemma->animative_name;
        }
        if ($lemma->named) {
            $grams[]= $lemma->named->name;
        }        
        
        return view('lemma.show')
                  ->with(['lemma'=>$lemma,
                          'grams'=>$grams
                         ]);
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
     * Count wordforms linked with lemmas,
     * fill in wordform_total
     */
    public function countWordforms() {
        $is_all_checked = false;
        while (!$is_all_checked) {
            $lemmas = Lemma::whereNull('wordform_total')
                    ->take(1000)
                    ->get();
            if (!sizeof($lemmas)) {
                $is_all_checked = true;
            }

            foreach ($lemmas as $lemma) {
    print "<p>".$lemma->lemma;  
                $wordform_count = $lemma->wordforms()->count();
print " = $wordform_count";
                $lemma->wordform_total = $wordform_count;
                $lemma->save();
            }            
        }
    }
    
    /**
     * Check all lemmas
     * if exists such lemma in Russian Wiktionary,
     * fill lang_pos_id = ID of lang_pos entry in Russian Wiktionary
     * OR = 0 if doesn't exist lemma in Russian Wiktionary
     */
    public function linkRuWikt() {
        $is_all_checked = false;
        while (!$is_all_checked) {
            $lemmas=Lemma::whereNull('in_wiktionary')
                    ->take(10)
                    ->get();
            //if (!sizeof($lemmas)) {
                $is_all_checked = true;
            //}

            foreach ($lemmas as $lemma) {
    print "<p>".$lemma->lemma." = ";  
                $lang_poses = LangPOS::getByLemma($lemma);
                if ($lang_poses) {
                    $in_wiktionary = 1;
                    $lemma
                            //->setConnection('mysql')
                            ->lang_poses()->detach();
                    foreach ($lang_poses as $lang_pos) {
                        $lemma->lang_poses()->attach($lang_pos->id);
print $lang_pos->id .", ";                
                    }
                } else {
                    $in_wiktionary = 0;
                }
                $lemma->in_wiktionary = $in_wiktionary;
                //$lemma->save();
            }            
        }
    }
    
}
