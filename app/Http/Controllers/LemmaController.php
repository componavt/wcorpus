<?php

namespace Wcorpus\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Response;

use Wcorpus\Models\Lemma;
use Wcorpus\Models\LemmaMatrix;
use Wcorpus\Models\Sentence;
use Wcorpus\Models\Wordform;
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
                    'search_wordform' => (int)$request->input('search_wordform'),
                    'search_lemma'    => $request->input('search_lemma'),
                    'search_lemma1'   => $request->input('search_lemma1'),
                    'search_lemma2'   => $request->input('search_lemma2'),
                    'search_id'       => $request->input('search_id'),
                    'order_by'        => $request->input('order_by'),
                    'limit_sentences' => $request->input('limit_sentences'),
                    'limit_lemmas'    => $request->input('limit_lemmas'),
                ];
        
        if (!$this->url_args['page']) {
            $this->url_args['page'] = 1;
        }
        
        if (!$this->url_args['search_id']) {
            $this->url_args['search_id'] = NULL;
        }
        
        if ($this->url_args['limit_num']<=0) {
            $this->url_args['limit_num'] = 10;
        } elseif ($this->url_args['limit_num']>1000) {
            $this->url_args['limit_num'] = 1000;
        }   
        
        if ($this->url_args['limit_sentences']<=0) {
            $this->url_args['limit_sentences'] = 100;
        }   

        if ($this->url_args['limit_lemmas']<=0) {
            $this->url_args['limit_lemmas'] = 1000;
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
        if (!$this->url_args['order_by']) {
            $this->url_args['order_by'] = 'lemma';
            $direction ='asc';
        }
        
        if ($this->url_args['order_by'] == 'freq') {
            $direction ='desc';
        }
        
        $lemmas = Lemma::
                orderBy($this->url_args['order_by'], $direction);

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
     * For the value entered (search_id), find all the lemmas from the context
     *
     * @return \Illuminate\Http\Response
     */
    public function searchContext()
    {
        $sentence_list = // array of sentences [text of sentence, wordforms joined with comma)
        $lemma_values = // array with searched lemmas for select field [5559=>'ЛЕВЫЙ (adjective)']
        $lemma_strings = // array of pairs lemma_id=>lemma_lemma
        $context_lemmas = []; // array of pairs lemma_id=>frequency in the context set
        
        if ($this->url_args['search_id']) {
            $lemma_id = $this->url_args['search_id'];
            $lemma_values[$lemma_id] = Lemma::getLemmaWithPOSByID($lemma_id);
            list($sentence_list,$context_lemmas, $lemma_strings) = 
                    Lemma::lemmaContext($lemma_id);
        }      
        
        return view('lemma.context')
              ->with(array(
                           'lemma_values'  => $lemma_values, 
                           'lemma_strings'  => $lemma_strings, 
                           'sentence_list' => $sentence_list, 
                           'context_lemmas'=> $context_lemmas, 
                           'args_by_get'   => $this->args_by_get,
                           'url_args'      => $this->url_args,
                          )
                    );
    }
    
    /**
     * For the value entered (search_id), find all the lemmas from the context
     *
     * @return \Illuminate\Http\Response
     */
    public function contextIntersection()
    {
        $lemma1_values = $lemma2_values = // arrays with searched lemmas for select field [5559=>'ЛЕВЫЙ (adjective)']
        $sentence_list = // array of sentences [text of sentence, wordforms joined with comma)
        $lemma_list = []; // array of pairs lemma_id=>frequency in the context set
        
        if ($this->url_args['search_lemma1']) {
            $lemma1_id = $this->url_args['search_lemma1'];
            $lemma1_values[$lemma1_id] = Lemma::getLemmaWithPOSByID($lemma1_id);
        }      
        
        if ($this->url_args['search_lemma2']) {
            $lemma2_id = $this->url_args['search_lemma2'];
            $lemma2_values[$lemma2_id] = Lemma::getLemmaWithPOSByID($lemma2_id);
        }      
        
        if ($this->url_args['search_lemma1'] && $this->url_args['search_lemma2']) {
            list($sentence_list1,$context_lemmas1, $lemma_strings1) = 
                    Lemma::lemmaContext($lemma1_id);

            list($sentence_list2,$context_lemmas2, $lemma_strings2) = 
                    Lemma::lemmaContext($lemma2_id);
            
            foreach (array_intersect(array_keys($sentence_list1),array_keys($sentence_list2)) as $sentence_id) {
                $sentence_list[$sentence_id]['sentence'] = $sentence_list1[$sentence_id]['sentence'];
                
                $sentence_list[$sentence_id]['wordforms'] = 
                        array_merge($sentence_list1[$sentence_id]['wordforms'],$sentence_list2[$sentence_id]['wordforms']);
                        
            }
            
            foreach (array_intersect(array_keys($context_lemmas1),array_keys($context_lemmas2)) as $lemma_id) {
                $lemma_list[$lemma_id]['lemma'] = $lemma_strings1[$lemma_id];
                $lemma_list[$lemma_id]['freq1'] = $context_lemmas1[$lemma_id];
                $lemma_list[$lemma_id]['freq2'] = $context_lemmas2[$lemma_id];
            }
//print "<P>".sizeof($lemma_list);            

//print "<P>".sizeof($context_lemmas1);            
//print "<P>".sizeof($context_lemmas2);            
            
            $dist = (sizeof($lemma_list)*sizeof($lemma_list)) / (sizeof($context_lemmas1)*sizeof($context_lemmas2));
        }
        
        return view('lemma.context_intersection')
              ->with(array(
                           'lemma1_values'  => $lemma1_values, 
                           'lemma2_values'  => $lemma2_values, 
                           'lemma_list'     => $lemma_list, 
                           'sentence_list' => $sentence_list, 
                           'dist' => $dist,
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
        if ($lemma->animative_name()) {
            $grams[]= $lemma->animative_name();
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
     * Count frequency of occurrence of each lemma in the texts under study
     * fill in freq
     */
    public function countFrequency() {
        $is_all_checked = false;
        while (!$is_all_checked) {
            $lemmas = Lemma::whereNull('freq')
                    ->take(1)
                    ->get();
            if (!sizeof($lemmas)) {
                $is_all_checked = true;
                continue;
            }

            foreach ($lemmas as $lemma) {
    print "<p>".$lemma->lemma;  
                $query = "SELECT count(*) as count FROM sentence_wordform where "
                       . "wordform_id in (select wordform_id from lemma_wordform "
                                       . "where lemma_id=".$lemma->id.")";
                $results = DB::select( DB::raw($query) );
                $freq = $results[0]->count;
print " = $freq";
                $lemma->freq = $freq;
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
    
    /**
     * Go around each sentence, consider word forms in pairs, which have lemmas. 
     * Record unique pairs in the database
     * select distinct sentence_id  from sentence_wordform order by sentence_id limit 1;
     * select * from sentence_wordform where sentence_id=46 order by word_number;
     * select lemma_id from lemma_wordform where wordform_id=1;
     * 
     * select l1.lemma,l2.lemma, freq_12, freq_21 from lemmas as l1, lemmas as l2, lemma_matrix where lemma_matrix.lemma1=l1.id and lemma_matrix.lemma2=l2.id order by freq_21 desc limit 50;
     * select l1.lemma, l2.lemma, p1.aot_name, p2.aot_name, freq_12, freq_21 from lemmas as l1, lemmas as l2, lemma_matrix, pos as p1, pos as p2 where lemma_matrix.lemma1=l1.id and lemma_matrix.lemma2=l2.id and l1.pos_id=p1.id and l2.pos_id=p2.id and freq_21=10 order by freq_21 desc limit 50;
     */
    public function createLemmaMatrix() {
        $is_all_checked = false;
        while (!$is_all_checked) {
            $sentences = DB::table('sentence_wordform')
                           ->select('sentence_id')
                           ->where('processed',0)
                           ->groupBy('sentence_id')
                           ->orderBy('sentence_id')
                           ->take(10)->get();
            if ($sentences->count()) {
                foreach($sentences as $sentence) {
//print "<p>".$sentence->sentence_id;                    
                    $wordforms = DB::table('sentence_wordform')
                           ->where('sentence_id', $sentence->sentence_id)
                           ->orderBy('word_number')
                           ->get();
    //print "<pre>";                
    //print_r($wordforms);   
                    if ($wordforms->count()>1) {
                        for ($i=1; $i<$wordforms->count(); $i++) {
                            $left_wordform_id = $wordforms[$i-1]->wordform_id;
                            $right_wordform_id = $wordforms[$i]->wordform_id;
    //print "<P>".$left_wordform_id.' - '.$right_wordform_id; 

                            $left_lemmas = DB::table('lemma_wordform')
                                   ->select('lemma_id')
                                   ->where('wordform_id', $left_wordform_id)
                                   ->orderBy('lemma_id')
                                   ->get();

                            $right_lemmas = DB::table('lemma_wordform')
                                   ->select('lemma_id')
                                   ->where('wordform_id', $right_wordform_id)
                                   ->orderBy('lemma_id')
                                   ->get();

                            foreach ($left_lemmas as $left_lemma) {
                                $left_lemma_id = $left_lemma->lemma_id;
                                foreach ($right_lemmas as $right_lemma) {
                                    $right_lemma_id = $right_lemma->lemma_id;
                                    if ($left_lemma_id != $right_lemma_id) {
                                        if ($left_lemma_id<$right_lemma_id) {
                                            $count12=1;
                                            $count21=0;
                                            $lemma1 = $left_lemma_id;
                                            $lemma2 = $right_lemma_id;
                                        } else {
                                            $count12=0;
                                            $count21=1;
                                            $lemma1 = $right_lemma_id;
                                            $lemma2 = $left_lemma_id;
                                        }
      print "<P>$left_wordform_id - $right_wordform_id = $lemma1 - $lemma2 = $count12 - $count21"; 
                                        $pair = LemmaMatrix::firstOrCreate([
                                                'lemma1'=>$lemma1, 
                                                'lemma2'=>$lemma2 
                                            ]);
                                        $pair->freq_12 += $count12;
                                        $pair->freq_21 += $count21;
                                        $pair->save();
                                    }
                                }                            
                            }
                        }
                    }
                    $query = "UPDATE sentence_wordform SET "
                           . "processed=1 WHERE sentence_id=".$sentence->sentence_id;
        //dd( "<P>$query");
                    $res = DB::statement($query);

                }
            }
            else {
                $is_all_checked = true;
            }
        }
    }
    
    /**
     * Gets list of lemmas for drop down list in JSON format
     * Test url: /lemma/list_with_pos
     * 
     * @return JSON response
     */
    public function listWithPOS(Request $request)
    {
        $search_name = '%'.$request->input('q').'%';

        $list = [];
        $lemmas = Lemma::where('lemma','like', $search_name)
                       ->take(100) 
                       ->orderBy('lemma')->get();
        foreach ($lemmas as $lemma) {
            $list[]=['id'  => $lemma->id, 
                     'text'=> $lemma->lemma. ' ('.$lemma->pos->name.')'];
        }  

        return Response::json($list);
    }

}
