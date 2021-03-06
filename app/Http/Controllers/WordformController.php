<?php

namespace Wcorpus\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use Wcorpus\Models\Lemma;
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
                    'search_lemma'  => (int)$request->input('search_lemma'),
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
        
        if ($this->url_args['search_lemma']) {
            $lemma_id = $this->url_args['search_lemma'];
        
            $wordforms = $wordforms->whereIn('id',function($query) use ($lemma_id){
                                $query->select('wordform_id')
                                ->from('lemma_wordform')
                                ->where('lemma_id', $lemma_id);
                            });  
            $lemma = Lemma::find($lemma_id)->lemma;
        } else {
            $lemma = '';
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
                               'lemma'         => $lemma,
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
/*        $wordform = new Wordform();
        $wordform->wordform = "проходило";
print "<pre>";
        $text_result = $wordform->lemmatize();
var_dump($text_result);
exit(0);*/
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
    
    /**
     * Processing after lemmatizing
     * Select wordforms with only one lemma (lemma_total=1)
     * fill in sentence_wordform.lemma_id by lemma_wordform.lemma_id
     * and sentence_wordform.lemma_found=1
     * 
     * select count(*) from sentence_wordform where lemma_found=1
     * select count(*) from wordforms where lemma_total=1
     * select sum(sentence_total) from wordforms where lemma_total=1
     * select count(*) from sentence_wordform, wordforms where sentence_wordform.wordform_id=wordforms.id and lemma_total=1 and lemma_found is null;
     *
     * @return \Illuminate\Http\Response
     */
    public function processWordformWithOneLemma()
    {
/*        // stop when there is no wordforms with one lemmas not processed
        $is_exist_not_processed = 1;
        while ($is_exist_not_processed) {
            $wordforms = Wordform::where('lemma_total',1)
                    ->whereHas('sentences', function ($query) {
                        $query->whereNull('lemma_found');
                    })
                    ->take(100)->get();
        
            if ($wordforms->count()) {
                foreach ($wordforms as $wordform) {
    print $wordform->id;
    print "<br>";
                    $query = "UPDATE sentence_wordform SET lemma_id=".$wordform->lemmas()->first()->id.
                             ", lemma_found=1 WHERE wordform_id=".$wordform->id;
        //print "<P>$query";            
                    $res = DB::statement($query);
//dd($res);                    
                }
                //$is_exist_not_processed = 0;
            } else {
                $is_exist_not_processed = 0;
            }
        }*/
/*        
        // stop when there is no wordforms with one lemmas not processed        
        $is_exist_word_not_processed = 1;
        while ($is_exist_word_not_processed) {
            $wordforms = Wordform::where('lemma_total',1)
                    ->where('processed',0)
                    ->take(100)->get();
            
            if ($wordforms->count()) {
                foreach ($wordforms as $wordform) {
    print $wordform->id;
    print ", ";
                    $lemma_id = $wordform->lemmas()->first()->id;                    
    print "$lemma_id<br>";
    //exit(0);
                    $is_exist_sent_not_processed = 1;
                    while ($is_exist_sent_not_processed) {
                        $sentences = DB::table('sentence_wordform')
                                ->select('sentence_id')
                                ->where('wordform_id',$wordform->id)
                                ->whereNull('lemma_id')
                                ->take(10000)->get();
                        
                        if ($sentences->count()) {
                            foreach($sentences as $sentence) {
                                $query = "UPDATE sentence_wordform SET "
                                       . "lemma_id=".$lemma_id
                                       . ", lemma_found=1 WHERE "
                                       . "wordform_id=".(int)$wordform->id
                                       . " and sentence_id=".(int)$sentence->sentence_id;
        //dd( "<P>$query");
                                $res = DB::statement($query);
                            }
                        } else {
                            $is_exist_sent_not_processed = 0;
                            $wordform->processed = 1;
                            $wordform->save();
                        }
                    }
                }
                //$is_exist_word_not_processed = 0;
            } else {
                $is_exist_word_not_processed = 0;
            }
        }
*/  
        // stop when there is no wordforms with one lemmas not processed        
        $is_exist_word_not_processed = 1;
        while ($is_exist_word_not_processed) {
            $wordforms = Wordform::where('lemma_total',1)
                    ->where('processed',0)
                    ->take(100)->get();
            
            if ($wordforms->count()) {
                foreach ($wordforms as $wordform) {
    print $wordform->id;
    print ", ";
                    $lemma_id = $wordform->lemmas()->first()->id;                    
    print "$lemma_id<br>";
    //exit(0);
                    $is_exist_sent_not_processed = 1;
                    while ($is_exist_sent_not_processed) {
                        $query = "UPDATE sentence_wordform SET "
                               . "lemma_found=1, lemma_id=".$lemma_id
                               . " WHERE wordform_id=".(int)$wordform->id
                               . " and (lemma_found is null or lemma_id is null)";
            //dd( "<P>$query");
                        $res = DB::statement($query);
                        $sentences_count = DB::table('sentence_wordform')
                                   ->select('sentence_id')
                                   ->where('wordform_id',$wordform->id)
                                   ->whereNull('lemma_id')->count();
                        if (!$sentences_count) {
                            $wordform->processed = 1;
                            $wordform->save();
                            $is_exist_sent_not_processed = 0;
                        }
                    }
                }
            } else {
                $is_exist_word_not_processed = 0;
            }
        }
  
 /*
        $is_exist_word_not_processed = 1;
        while ($is_exist_word_not_processed) {
            $wordforms = mysql_query("select id from wordforms where lemma_total=1 and processed=0 LIMIT 0,100") 
                    or die('error 1');
        
            if (mysql_num_rows($wordforms)) {
                while($wordform = mysql_fetch_assoc($wordforms)) {
    print $wordform['id'];
    print "<br>";
                    $lemma = mysql_fetch_assoc(mysql_query("select lemma_id as id from lemma_wordform"
                            . " where wordform_id=".$wordform['id']));
                    $is_exist_sent_not_processed = 1;
                    while ($is_exist_sent_not_processed) {
                        $sentences = mysql_query("select sentence_id as id from sentence_wordform"
                                . " where wordform_id=".$wordform['id']
                                . " and (lemma_found is null or lemma_id is null) LIMIT 0,100") 
                                or die('error 2');
                        
                        if (mysql_num_rows($sentences)) {
                            while($sentence = mysql_fetch_assoc($sentence)) {                                
                                $query = "UPDATE sentence_wordform SET "
                                       . "lemma_found=1, lemma_id=".$lemma['id']
                                       . " WHERE wordform_id=".(int)$wordform['id']
                                       . " and sentence_id=".(int)$sentence['id'];
        //dd( "<P>$query");
                                mysql_query($query) or die('error 3');
                            }
                        } else {
                            $is_exist_sent_not_processed = 0;
                            $query = "UPDATE wordforms SET processed=1"
                                   . " WHERE id=".(int)$wordform['id'];
                            mysql_query($query) or die('error 4');
                        }
                    }
                }
                //$is_exist_word_not_processed = 0;
            } else {
                $is_exist_word_not_processed = 0;
            }
        }
 */       
    }
    
    /**
     * Processing after lemmatizing
     * Select wordforms without lemmas (lemma_total=0)
     * fill in sentence_wordform.lemma_found=0
     * 
     * select count(*) from sentence_wordform,wordforms where lemma_found is null and sentence_wordform.wordform_id=wordforms.id and lemma_total=0
     *
     * @return \Illuminate\Http\Response
     */
    /*
    public function processWordformWithoutLemmas()
    {
        // stop when there is no wordforms without lemmas
        $is_exist_without_lemmas = 1;

        while ($is_exist_without_lemmas) {
            $wordforms = Wordform::where('lemma_total',0)
                    ->whereHas('sentences', function ($query) {
                        $query->whereNull('lemma_found');
                    })
                    ->take(100)->get();
        
            if ($wordforms->count()) {
                foreach ($wordforms as $wordform) {
                    $query = "UPDATE sentence_wordform SET lemma_found=0 "
                           . "WHERE wordform_id=".$wordform->id;
        //print "<P>$query";            
                    DB::statement($query);
                }
            } else {
                $is_exist_without_lemmas = 0;
            }
        }
    }*/
    
    /**
     * Processing after lemmatizing
     * Select wordforms without lemmas (lemma_total=0)
     * delete them and their links with sentences
     * 
     * select count(*) from wordforms where lemma_total=0
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteWordformWithoutLemmas()
    {
        // stop when there is no wordforms without lemmas
        $is_exist_without_lemmas = 1;

        while ($is_exist_without_lemmas) {
            $wordforms = Wordform::where('lemma_total',0)
                ->take(100)->get();
            if ($wordforms->count()) {
                foreach ($wordforms as $wordform) {
    print $wordform->id;
    print "<br>";
                    $wordform->sentences()->detach();
                    $wordform->delete();
                }
            } else {
                $is_exist_without_lemmas = 0;
            }
        }
    }
}
