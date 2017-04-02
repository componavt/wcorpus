<?php

namespace Wcorpus\Http\Controllers;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;

use DB;

use Wcorpus\Models\Author;
use Wcorpus\Models\Publication;
use Wcorpus\Models\Text;

class TextController extends Controller
{
    public $url_args=[];
    public $args_by_get='';
    
     /**
     * Instantiate a new new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        // permission= dict.edit, redirect failed users to /dict/lemma/, authorized actions list:
        $this->middleware('auth', 
                          ['only' => ['create','store','edit','update','destroy',
                                      'extractFromWikiSource']]);
        
        $this->url_args = [
                    'limit_num'       => (int)$request->input('limit_num'),
                    'page'            => (int)$request->input('page'),
                    'search_title'  => $request->input('search_title'),
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
                select('id', 'title','author_id','publication_id')->
                orderBy('title');

        if ($this->url_args['search_title']) {
            $texts = $texts->where('title','like', $this->url_args['search_title']);
        } 

        if ($this->url_args['search_author']) {
            $texts = $texts->where('author_id',$this->url_args['search_author']);
        } 
        
        $numAll = $texts->get()->count();

        $texts = $texts->with('author')
                ->paginate($this->url_args['limit_num']);         
        
        $author_values = Author::getListWithQuantity('texts');        
        
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
     * Parse wikitext,
     * search author name, publication title, creation date, text of publication
     *
     * @return \Illuminate\Http\Response
     */
    public function parseWikitext()
    {
//select substring(`wikitext`,1,10) as beg, count(*) as count from texts group by beg order by count desc limit 0,100;
        
        // all texts have not empty wikitext, stop when there is no empty (null) texts 
        $is_exist_not_parse_text = 1;
        
        while ($is_exist_not_parse_text) {
            $texts=Text::whereNull('text')->orderBy('title')->take(100)->get();
//dd($texts);            
            if ($texts) {
                foreach ($texts as $text) {
print "<p>".$text->id;                    
                    $text->author_id = Author::parseWikitext($text->wikitext);
                    
                    $text_info = Text::parseWikitext($text->wikitext);
                    $text->text = $text_info['text'];
                    
                    $text->publication_id = Publication::parseWikitext(
                                                            $text->wikitext, 
                                                            $text->author_id,
                                                            $text_info['title'],
                                                            $text_info['creation_date']
                            );

                    $text->push();
                }
                $is_exist_not_parse_text = 0;                
            } else {
                $is_exist_not_parse_text = 0;
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
}
