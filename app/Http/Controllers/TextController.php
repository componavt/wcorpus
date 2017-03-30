<?php

namespace Wcorpus\Http\Controllers;

use Illuminate\Http\Request;

use DB;

use Wcorpus\Models\Text;

class TextController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * Extract texts from wikisource,
     * fill table texts this data
     * id=wikisource.page.page_id
     * id=wikisource.text.old_id=wikisource.page.page_latest
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
        $latest_text = Text::orderBy('id','desc')->first();
        $from_id= $latest_text->id;
//dd($from_id);       
        $pages = DB::connection('wikisource')
                ->table('page')
                ->where('page_namespace',0)
                ->where('page_is_redirect',0)
                ->where('page_latest','>',$from_id)
                ->select(DB::raw('page_latest, page_title'))
                ->orderBy('page_latest')
                ->take(50000)
                ->get();
        
        foreach ($pages as $page) {
            $text = DB::connection('wikisource')
                    ->table('text')
                    ->select('old_text')
                    ->where('old_id', $page->page_latest)
                    ->first();
                    
            DB::connection('mysql')->table('texts')->insert([
                    'id' => $page->page_latest,
                    'title' => $page->page_title,
                    'wikitext' => $text->old_text,
                    'text' => null
                ]
            );
            
        }
print $pages->count();        
    }
}
