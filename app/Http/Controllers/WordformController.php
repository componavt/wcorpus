<?php

namespace Wcorpus\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use Wcorpus\Models\Sentence;
use Wcorpus\Models\Wordform;
use Wcorpus\Wcorpus;

class WordformController extends Controller
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
                    'search_sentence'  => (int)$request->input('search_sentence'),
                    'search_wordform'  => $request->input('search_wordform'),
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
        $wordforms = Wordform::
                select('id')->
                orderBy('wordform');
                //->orderBy('id');

        if ($this->url_args['search_sentence']) {
            $sentence_id = $this->url_args['search_sentence'];
        
            $wordforms = $wordforms->whereIn('id',function($query) use ($sentence_id){
                                $query->select('wordform_id')
                                ->from('sentence_wordform')
                                ->where('sentence_id', $sentence_id);
                            });  
            $sentence = Sentence::find($sentence_id)->sentence;
        } else {
            $sentence = '';
        }
        
        if ($this->url_args['search_wordform']) {
            $wordforms = $wordforms->where('wordform','like',$this->url_args['search_wordform']);
        } 
        
        $numAll = $wordforms->get()->count();

        $wordforms = $wordforms
                ->paginate($this->url_args['limit_num']);         
        
        
            return view('wordform.index')
                  ->with(array(
                               'numAll'        => $numAll,
                               'sentence'      => $sentence,
                               'wordforms'     => $wordforms,
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
     * Check sentences with 1 word
     * check this word if has alphabetic simbols
     * Delete failed words without alphabetic simbols
     */
    /*
    public function deleteBadWordforms() {
        $is_all_checked = false;
        while (!$is_all_checked) {
 /*           
            $sentences = DB::table('sentences')->select('id')->where('wordform_total',1)
                    ->whereNull('is_checked')
                    ->take(10)
                    ->get();
            if (!sizeof($sentences)) {
                $is_all_checked = true;
            }

            foreach ($sentences as $sentence) {
    print "<p>sentence=".$sentence->id;//'<br>'.$sentence->sentence;            
                $wordforms = DB::table('sentence_wordform')
                        ->join('wordforms','wordforms.id','=','sentence_wordform.wordform_id')
                        ->select('wordform')
                        ->where('sentence_id',$sentence->id)->get();
                $init_count = $count = sizeof($wordforms);
                if ($count) {
                    foreach ($wordforms as $wordform) {
    print "<br>".$wordform->wordform;                        
                        if (!preg_match("/[[:alpha:]]/u",$wordform->wordform)) {
    print "= deleted\n";                        
                            //$wordform->sentences()->detach();
                            //$wordform->delete();
                            $count--;
                        } 
                    }
                } 
                //$sentence->wordform_total = $count;
                //$sentence->is_checked = 1;
                //$sentence->save();
            }
*/
    /*
            $sentences = Sentence::where('wordform_total',1)
                    ->whereNull('is_checked')
                    //->where('sentence','like','%9%')
                    ->take(1000)
                    ->get();
            if (!sizeof($sentences)) {
                $is_all_checked = true;
            }

            foreach ($sentences as $sentence) {
    print "<p>sentence=".$sentence->id;//'<br>'.$sentence->sentence;            
                $wordforms = $sentence->wordforms;
                //$init_count = 
                $count = $sentence->wordforms()->count();
                if ($wordforms) {
                    foreach ($wordforms as $wordform) {
    print "<br>".$wordform->wordform;                        
    //                    if (!preg_match("/([[:alpha:]]+['-])*[[:alpha:]]+'?/u",$wordform->wordform)) {
                        if (!preg_match("/[[:alpha:]]/u",$wordform->wordform)) {
    print "= deleted\n";                        
                            $wordform->sentences()->detach();
                            $wordform->delete();
                            $count--;
                        } 
                    }
                } 
                $sentence->wordform_total = $count;
                $sentence->is_checked = 1;
                $sentence->save();
            }
            
        }
    }
    
    /**
     * Check wordforms with ending apostroph
     * Delete ending apostroph, 
     * check 1) if such wordform is exists in table, 
     *          delete all links of this wordform with sentences
     *          link all sentences with founded wordform,
     *          delete this wordform
     *       2) else save wordform without apostroph
     */
    public function deleteWordsWithApostroph() {
        $is_all_checked = false;
        while (!$is_all_checked) {
            $wordforms = Wordform::where('wordform','like','%\'')
                    //->where('sentence','like','%9%')
                    ->take(100)
                    ->get();
            if (!sizeof($wordforms)) {
                $is_all_checked = true;
            }

            foreach ($wordforms as $wordform) {
    print "<p>wordform=".$wordform->wordform;//'<br>'.$sentence->sentence;  
                if (preg_match("/^(.+)\'$/",$wordform->wordform,$regs)) {
                    $new_wordform = $regs[1];
                    $such_wordform = Wordform::where('wordform',$new_wordform)->first();
                    if (!$such_wordform) {
print "<br>$new_wordform";                        
                        $wordform->wordform = $new_wordform;
                        $wordform->save();
print " saved";                        
                    } else {
                        $sentences = $wordform->sentences;
                        foreach ($sentences as $sentence) {
print "<br>".$such_wordform->id.", ".$sentence->id.", ".$sentence->pivot->word_number;                        
//dd($sentence);                            
                            $such_wordform->sentences()->attach($sentence->id,
                                    ['word_number'=>$sentence->pivot->word_number]);
                            $wordform->sentences()->detach($sentence->id);
print " saved";                        
                        }
                        $wordform->delete();
                    }
                }
            }
            
        }
    }
    
    /**
     * Count sentences linked with wordforms,
     * fill in sentence_total
     */
    public function countSentences() {
        $is_all_checked = false;
        while (!$is_all_checked) {
            $wordforms = Wordform::whereNull('sentence_total')
                    ->take(1000)
                    ->get();
            if (!sizeof($wordforms)) {
                $is_all_checked = true;
            }

            foreach ($wordforms as $wordform) {
    print "<p>".$wordform->wordform;  
                $sentence_count = $wordform->sentences()->count();
print " = $sentence_count";
                $wordform->sentence_total = $sentence_count;
                $wordform->save();
            }            
        }
    }
    
    /**
     * Lemmatize a words
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function lemmatize($id)
    {
        $wordform=Wordform::find($id);                

        $wordform->update_lemmas();
    }
    
    /**
     * Lemmatize all words
     *
     * @return \Illuminate\Http\Response
     */
    public function lemmatizeAll()
    {
        // stop when there is no sentences with wordform_total=NULL
        $is_exist_not_lemmatized = 1;
        
        while ($is_exist_not_lemmatized) {
            $wordforms=Wordform::
                    whereNull('lemma_total')
                    //->whereNotIn('id',[240,1159])
                    ->take(1000)
                    ->get();
            if ($wordforms->count()) {
                foreach ($wordforms as $wordform) {
print "<p><b>".$wordform->wordform."</b> (".$wordform->id.")\n";                    
                    $wordform->update_lemmas();
                }
            } else {
                $is_exist_not_lemmatized = 0;
            }
        }
    }
}
