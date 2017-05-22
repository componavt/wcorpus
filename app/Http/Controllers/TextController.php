<?php

namespace Wcorpus\Http\Controllers;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;

use DB;

use Wcorpus\Models\Author;
use Wcorpus\Models\Publication;
use Wcorpus\Models\Sentence;
use Wcorpus\Models\Text;

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
                                      'breakText','breakAllText',
                                      'extractFromWikiSource']]);
        
        $this->url_args = [
                    'limit_num'       => (int)$request->input('limit_num'),
                    'page'            => (int)$request->input('page'),
                    'search_title'  => $request->input('search_title'),
                    'search_wikitext'  => $request->input('search_wikitext'),
                    'search_author'  => (int)$request->input('search_author'),
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
        
        $this->args_by_get = Text::searchValuesByURL($this->url_args);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $texts = Text::
//                select('id', 'title','author_id','publication_id')->
                select('id');
//                where('id','<',2000)->
//                whereNotNull('text');
//                ->orderBy('title');
//dd($texts->get()->count());
        if ($this->url_args['search_title']) {
            $texts = $texts->where('title','like', $this->url_args['search_title']);
        } 

        if ($this->url_args['search_wikitext']) {
            $texts = $texts->where('wikitext','like', $this->url_args['search_wikitext']);
        } 

        if ($this->url_args['search_author']) {
            $texts = $texts->where('author_id',$this->url_args['search_author']);
        } 
        
        $numAll = $texts->get()->count();

        $texts = $texts
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

    /**
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

    /**
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
    public function breakAllText()
    {
        // stop when there is no texts with sentence_total=NULL
        $is_exist_not_broken_text = 1;
        
        while ($is_exist_not_broken_text) {
            $texts=Text::
                    whereNull('sentence_total')
                    ->whereNotIn('id',[21530,402631,125263,125413])
                    ->orderBy('title')
                    ->take(100)
                    ->get();
//dd($texts);            
            if ($texts->count()) {
                foreach ($texts as $text) {
print "<p>".$text->id."</p>\n";                    
                    $text->breakIntoSentences();
                }
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
//SELECT old_id,page_title,old_text FROM page,text WHERE page_namespace=0 AND page.page_latest=text.old_id order by old_id limit 0,10;
/*        $pages = DB::connection('wikisource')
//                ->table('page')->where('page_namespace',0)->orderBy('page_id')->-take(10)->get();
                ->table('text')
                ->select(DB::raw('old_id, page_title, old_text'))
                ->join('page', 'page.page_latest', '=', 'text.old_id')
                ->where('page_namespace',0)
//                ->orderBy('old_id')
                ->get(); */
        
        $is_exist_new_pages = 1;
        $portion = 100;
        $incorrect_texts = [39563,291621];
        $without_ids = join(',',$incorrect_texts);
        
        while ($is_exist_new_pages) {
            $latest_text = Text::orderBy('id','desc')->first();
            if ($latest_text) {
                $from_id = $latest_text->id;
            } else {
                $from_id = 1;
            }

// can not to insert pages with id (39563,291621)
//$from_id = 291621;

            $pages = DB::connection('wikisource')
                    ->table('page')
                    ->where('page_namespace',0)
                    ->where('page_is_redirect',0)
                    ->whereNotIn('page_id',$without_ids)
                    ->where('page_id','>',$from_id)
                    ->select(DB::raw('page_id,page_latest, page_title'))
                    ->orderBy('page_id')
                    ->take($portion)
                    ->get();
            if (!sizeof($pages)) {
                $is_exist_new_pages = 0;                
            }
            foreach ($pages as $page) {
                $text = DB::connection('wikisource')
                        ->table('text')
                        ->select('old_text')
                        ->where('old_id', $page->page_latest)
                        ->where('old_text', 'not like', '#перенаправление%')
                        ->first();

                DB::connection('mysql')->table('texts')->insert([
                        'id' => $page->page_id,
                        'page_latest' => $page->page_latest,
                        'title' => $page->page_title,
                        'wikitext' => $text->old_text,
                        'text' => null
                    ]
                );

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
        $texts = Text::where('title','like', $search_title)
                       ->orderBy('title')->get();
        foreach ($texts as $text) {
            $list[]=['id'  => $text->id, 
                           'text'=> $text->title];
        }  

        return Response::json($list);
    }

}
