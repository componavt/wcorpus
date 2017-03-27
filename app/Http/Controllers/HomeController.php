<?php

namespace Wcorpus\Http\Controllers;

use Illuminate\Http\Request;

use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//SELECT old_text FROM page,text WHERE page.page_title='"+page_title+"' AND page_namespace=0 AND page.page_latest=text.old_id
        $page = DB::connection('wikisource')
//                ->table('page')->where('page_namespace',0)->orderBy('page_id')->-take(10)->get();
                ->table('text')
                ->join('page', 'page.page_latest', '=', 'text.old_id')
                ->where('page_namespace',0)
                ->orderBy('page_id')->take(10)->get();
dd($page);        
        return view('home')->with(['text'=>$page->page_content_model]);
    }
}
