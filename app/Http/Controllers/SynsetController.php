<?php

namespace Wcorpus\Http\Controllers;

use Illuminate\Http\Request;

use DB;

use Wcorpus\Models\Synset;

class SynsetController extends Controller
{
     /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->middleware('auth', 
                          ['only' => ['create','store','edit','update','destroy']]);
        
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = "SELECT DISTINCT lemma_id FROM synsets";
        $lemma_res = DB::select(DB::raw($query));
        $lemmas = [];
        foreach ($lemma_res as $lemma) {
            $lemma[$lemma->lemma_id] = Synset::where('lemma_id',$lemma->lemma_id)
//                                             ->select()
                                             ->orderBy('meaning_n')->get();
        }
            return view('synset.index')
                  ->with(['lemmas' => $lemmas]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $new_meaning_n = 1;
        return view('dict.lemma.create')
                  ->with(['new_meaning_n' => $new_meaning_n]);
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
}
