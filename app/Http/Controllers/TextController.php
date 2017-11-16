<?php

namespace Wcorpus\Http\Controllers;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;

use DB;
use Response;

use Wcorpus\Models\Author;
use Wcorpus\Models\Publication;
use Wcorpus\Models\Sentence;
use Wcorpus\Models\Text;
use Wcorpus\Wcorpus;

use Wcorpus\Wikiparser\TemplateExtractor;

class TextController extends Controller
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
                                      'parseWikitext','parseAllWikitext',
                                      'breakText','breakAllTexts',
                                      'extractFromWikiSource']]);
        
        $this->url_args = [
                    'limit_num'       => (int)$request->input('limit_num'),
                    'page'            => (int)$request->input('page'),
                    'search_title'  => $request->input('search_title'),
                    'search_wikitext'  => $request->input('search_wikitext'),
                    'search_author'  => (array)$request->input('search_author'),
                    'search_sentence'  => $request->input('search_sentence'),
                    'search_included'  => $request->input('search_included'),
//                    'search_id'       => (int)$request->input('search_id'),
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
//dd($this->url_args['search_author']);

        $texts = Text::
//                select('id', 'title','author_id','publication_id')->
                select('id')
//                where('id','<',2000)->
//                whereNotNull('text');
                ->orderBy('title');
//dd($texts->get()->count());
        if ($this->url_args['search_title']) {
            $texts = $texts->where('title','like', $this->url_args['search_title']);
        } 

        if ($this->url_args['search_wikitext']) {
            $texts = $texts->where('wikitext','like', $this->url_args['search_wikitext']);
        } 

        if ($this->url_args['search_author']) {
            $texts = $texts->whereIn('author_id',$this->url_args['search_author']);
        } 
        
        if ($this->url_args['search_sentence']<>'') {
            $texts = $texts->where('sentence_total', (int)$this->url_args['search_sentence']);
        } 

        if ($this->url_args['search_included']<>'') {
            $texts = $texts->where('included', (int)$this->url_args['search_included']);
        } 

        $numAll = $texts->get()->count();

        $texts = $texts->take(1000)
                //->with('author')
                ->paginate($this->url_args['limit_num']);         
        
        $author_values = Author::getListWithQuantity('texts');        
//dd($texts);
        
            return view('text.index')
                  ->with(array(
                               'author_values' => $author_values,
                               'numAll'        => $numAll,
                               'texts'         => $texts,
                               'args_by_get'    => $this->args_by_get,
                               'url_args'       => $this->url_args,
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

    /** SHOW()
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $text = Text::find($id);
        
        if (!$text) {
            return Redirect::to('/text/')
                           ->withErrors('The text with ID='. $id .' is not found');            
        }
        
        return view('text.show')
                  ->with([
                      'text'        => $text,
                      'args_by_get' => $this->args_by_get,
                      'url_args'    => $this->url_args,
                      ]);
    }

    /** EDIT()
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $text = Text::find($id); 
        
        return view('text.edit')
                  ->with([
                      'text'        => $text,
                      'args_by_get' => $this->args_by_get,
                      'url_args'    => $this->url_args,
                      ]);
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
        $text= Text::findOrFail($id);
        
        $this->validate($request, [
            'wikitext'  => 'required',
        ]);
        
        $request->wikitext = str_replace("\{","{",$request->wikitext);
        
        $text->wikitext = $request->wikitext;
        $text->save();
        
        return Redirect::to('/text/'.($text->id).($this->args_by_get))
                       ->withSuccess('Text is modified');
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
     * Parse wikitext,
     * search author name, publication title, creation date, text of publication
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function parseWikitext($id)
    {
        $text=Text::find($id);                

        if ($text->publication) {
            if ($text->publication->author) {
                $text->publication->author_id = null;
                $text->author_id = null;
                $text->publication->author()->delete();
            }
            $text->publication_id = null;
            $text->publication()->delete();
        }
        $text->parseData();
        
        $text->deleteSentences();
        $text->breakIntoSentences();
        
        return Redirect::to('/text/'.($text->id).($this->args_by_get))
                       ->withSuccess('Text is re-parsed');
    }
    
    /**
     * Parse wikitext,
     * search author name, publication title, creation date, text of publication
     *
     * @return \Illuminate\Http\Response
     */
    public function parseAllWikitext()
    {
//select substring(`wikitext`,1,10) as beg, count(*) as count from texts group by beg order by count desc limit 0,100;
        
        // all texts have not empty wikitext, stop when there is no empty (null) texts 
        $is_exist_not_parse_text = 1;
        
        while ($is_exist_not_parse_text) {
            $texts=Text::
                    whereNull('text')
                    //->orWhere('text','')
                    ->orderBy('title')
                    ->take(100)
                    ->get();
            //$is_exist_not_parse_text = 0;     // когда оттестится, убрать           
//dd($texts);            
            if ($texts->count()) {
                foreach ($texts as $text) {
                    $text->parseData();
                }
            } else {
                $is_exist_not_parse_text = 0;
            }
        }
    }
    
    /**
     * Break a text into sentences
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function breakText($id)
    {
        $text=Text::find($id);                

        if ($text->sentences()->count()) {
            $text->sentences()->delete();
        }
        
        $text->breakIntoSentences();
    }
    
    /**
     * Break all texts into sentences
     *
     * @return \Illuminate\Http\Response
     */
    public function breakAllTexts()
    {
        // stop when there is no texts with sentence_total=NULL
        $is_exist_not_broken_text = 1;
        
        while ($is_exist_not_broken_text) {
            $texts=Text::
                    whereNull('sentence_total')
                    ->whereNotIn('id',[402631,125263,125413])
//                    ->whereIn('author_id',[62,298,423])
                    ->where('included',1)
//                    ->orderBy('title')
                    ->take(10)
                    ->get();
//dd($texts);            
            if ($texts->count()) {
                foreach ($texts as $text) {
print "<p>".$text->id."</p>\n";                    
                    $text->breakIntoSentences();
                }
//                $is_exist_not_broken_text = 0;
            } else {
                $is_exist_not_broken_text = 0;
            }
        }
    }
    
    /**
     * Extract texts from wikisource (mediawiki database),
     * fill table texts this data
     * id=wikisource.page.page_id
     * page_latest=wikisource.text.old_id=wikisource.page.page_latest
     * title = wikisource.page.page_title
     * wikitext=wikisource.text.old_text
     *
     * @return \Illuminate\Http\Response
     */
    public function extractFromWikiSource()
    {
        $is_exist_new_pages = 1;
        $portion = 100;
        $incorrect_texts = [39563,291621];
        
        while ($is_exist_new_pages) {
            $latest_text = Text::orderBy('id','desc')->first();
            if ($latest_text) {
                $from_id = $latest_text->id;
            } else {
                $from_id = 1;
            }
//dd($from_id);
            $pages = DB::connection('wikisource')
                    ->table('page')
                    ->where('page_namespace',0)
                    ->where('page_is_redirect',0)
                    ->whereNotIn('page_id',$incorrect_texts)
                    ->where('page_id','>',$from_id)
                    ->leftJoin('text','text.old_id','=','page.page_latest')
                    ->where('old_text', 'not like', '#перенаправление%')
                    ->select(DB::raw('page_id,page_latest, page_title, old_text'))
                    ->orderBy('page_id')
                    ->take($portion)
                    ->get();
//dd($pages);            
            if (!sizeof($pages)) {
                $is_exist_new_pages = 0;   
                continue;
            }
            foreach ($pages as $page) {
                DB::connection('mysql')->table('texts')->insert([
                        'id' => $page->page_id,
                        'page_latest' => $page->page_latest,
                        'title' => $page->page_title,
                        'wikitext' => $page->old_text,
                        'text' => null
                    ]
                );
//                $from_id = $page->page_id;
            }
//print $pages->count();   
        }
        print 'done.';
    }
    
    /**
     * Collect statistics about which templates are contained in the wiki texts
     *
     * @return \Illuminate\Http\Response
     */
    public function templateStats()
    {
        $templates=[];
        // обойти все wikitext и посчитать сколько и каких шаблонов есть
        $texts = Text::select('wikitext')->where('wikitext','like','%{{%')->orderBy('id')
                //->take(10)
                ->get();
print sizeof($texts);
        foreach ($texts as $text) {
            if (preg_match_all("/\{\{([^\|\}]+)[\|\}]/",$text->wikitext, $regs, PREG_PATTERN_ORDER)) {
                foreach ($regs[1] as $temp_name) {
                    $temp_name = trim($temp_name);
                    if (isset($templates[$temp_name])) {
                        $templates[$temp_name] ++;
                    } else {
                        $templates[$temp_name] = 1;
                    }
                }
            }
        }

        arsort($templates);
//dd($templates);        
        print "<table border=\"1\">";
        $i=1;
        foreach ($templates as $temp=>$count) {
            print "<tr><td>$i</td><td>".str_replace("<","&lt;",$temp)."</td><td>$count</td></tr>\n";            
            $i++;
        }
        print "</table>";
    }
    
    /**
     * Gets list of titles for drop down list in JSON format
     * Test url: /text/title_list
     * 
     * @return JSON response
     */
    public function titlesList(Request $request)
    {
        $search_title = '%'.$request->input('q').'%';

        $list = [];
        $query = "SELECT id, title FROM texts where title like '$search_title' order by title limit 100";
        $texts = DB::select(DB::raw($query));
        
/*        
        $texts = Text::where('title','like', $search_title)
                       ->take(1000)
                       ->orderBy('title')->get();
*/
        foreach ($texts as $text) {
            $list[]=['id'  => $text->id, 
                           'text'=> $text->title];
        }  

        return Response::json($list);
    }

    /**
     * Get sentences and output into files
     * <id text>.txt 
     * A new sentences (without \r\n) begins in a new line
     *
     * @return \Illuminate\Http\Response
     */
    public function sentencesToFile()
    {
        $dirname = "/data/all/projects/git/wcorpus.addon/sentences/";
        $texts = Text::select('id')->orderBy('id')->get();
        foreach ($texts as $text) {
            $sentences = Sentence::where('text_id',$text->id)->orderBy('id');
            if ($sentences->count()) {
                $fh = fopen($dirname.$text->id.".txt",'w');
                foreach ($sentences->get() as $sentence) {
                    fwrite($fh,$sentence->sentence."\n");
                }
                fclose($fh);   
            }
            print "<p>".$text->id.".txt записан.</p>\n";
        }
    }
    
    /**
     * Count in text with ID number of old letters ѢѣѲѳIiѴѵ
     * and fill in the field old_letter_total
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function countOldLetters($id)
    {
        $text=Text::find($id);                
        if ($text->text) {
            $letters = 'ѢѣѲѳѴѵ'; 
            $letter_arr = preg_split('//u',$letters, null, PREG_SPLIT_NO_EMPTY);
            $count = 0;
//            print $text->text;
//            print "<hr>";
            
            foreach ($letter_arr as $letter) {
                print "<p>$letter = ".mb_substr_count($text->text, $letter);
                $count += mb_substr_count($text->text, $letter);
            }
            
            print "<p>Total: $count";
            $text->old_letter_total = $count;
            $text->save();
        }
    }
    
    /**
     * Count in all texts with old_letter_total=NULL number of old letters ѢѣѲѳIiѴѵ
     * and fill in the field old_letter_total
     * 
     * select count(*) from texts where old_letter_total is null;
     * 
     * select old_letter_total, sentence_total, count(*) from texts where old_letter_total>0 group by old_letter_total, sentence_total order by old_letter_total, sentence_total limit 50;
     * select old_letter_total, count(*) from texts where sentence_total>0 and old_letter_total is not null group by old_letter_total;
     * 
     * update texts set old_letter_total=null,old_to_letters=null;
     * 
     * @return \Illuminate\Http\Response
     */
    public function countOldLettersInAllTexts()
    {
        $is_exist_not_counted_texts = 1;
        $letters = 'ѢѣѲѳѴѵ'; 
        $letter_arr = preg_split('//u',$letters, null, PREG_SPLIT_NO_EMPTY);

        
        while ($is_exist_not_counted_texts) {
            $texts=Text::whereNull('old_letter_total')
                       ->select(DB::raw('id, old_letter_total, char_length(text) as length, text'))
//                       ->whereNotNull('text')
                       ->take(100)->get(); 
            if ($texts->count()) {
                foreach ($texts as $text) {
                    $count = 0;
                    if ($text->text) {
                        foreach ($letter_arr as $letter) {
                            $count += mb_substr_count($text->text, $letter);
                        }
                        $old_to_letters = $count/ $text->length;
                    } else {
                        $old_to_letters = 0;                        
                    }
                    print "<p>".$text->id."= $count, $old_to_letters</a>";
                    $text->old_letter_total = $count;
                    $text->old_to_letters = $old_to_letters;
                    $text->save();
                }
            } else {
                $is_exist_not_counted_texts = 0;                
            }
        }
    }
    
    /**
     * Calculate in all texts with old_to_letters=NULL relation number of old letters to all letters
     * and fill in the field old_to_letters
     * 
     * select count(*) from texts where old_to_letters is null;
     * 
     * select round(old_to_letters,2) as old,count(*) from texts group by old order by old;

     * @return \Illuminate\Http\Response
     */
    public function calculateOldToLetters()
    {
        $is_exist_not_counted_texts = 1;
        
        while ($is_exist_not_counted_texts) {
            $texts=Text::whereNull('old_to_letters')
                       ->select(DB::raw('id, old_letter_total, char_length(text) as length'))
                       ->take(100)->get(); 
            if ($texts->count()) {
                foreach ($texts as $text) {
                    $count = 0;
                    if ($text->length>0) {
                        $count = $text->old_letter_total / $text->length;
                    }
                    print "<p>".$text->id."= $count</a>";
                    $text->old_to_letters = $count;
                    $text->save();
                }
                //$is_exist_not_counted_texts = 0;                
            } else {
                $is_exist_not_counted_texts = 0;                
            }
        }
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @param  int  $included (0 or 1)
     * @return \Illuminate\Http\Response
     */
    public function changeIncluded(Request $request, $id, $included)
    {
        $text= Text::findOrFail($id);
        
        if ($included != '' && $included>0) {
            $included = 1;
        } else {
            $included = 0;
        }
        
        $text->included = $included;
        $text->save();
        
        if ($included==0) {
            $text->deleteSentences();
        } else {
            $text->breakIntoSentences();
        }
        
        return Redirect::to('/text/'.($text->id).($this->args_by_get))
                       ->withSuccess('Text is '.($included ? 'in' : 'ex').'cluded');
    }

}
