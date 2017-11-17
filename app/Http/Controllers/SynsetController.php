<?php

namespace Wcorpus\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Redirect;

use Wcorpus\Models\Lemma;
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
            $lemmas[$lemma->lemma_id] = Synset::where('lemma_id',$lemma->lemma_id)
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
        return view('synset.create')
                  ->with(['new_meaning_n' => $new_meaning_n]);
    }

    /**
     * Shows the form for creating a new synset.
     * 
     * Called by ajax request
     *
     * @return \Illuminate\Http\Response
     */
    public function createSynset(Request $request)
    {
        $count = (int)$request->input('count');
        $meaning_n = (int)$request->input('meaning_n');
                                
        return view('synset._form_create_synset')
                  ->with(['count' => $count,
                          'new_meaning_n' => $meaning_n
                         ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'lemma_id' => 'required|max:255',
        ]);
        $lemma_id = (int)$request->lemma_id;
        $new_synsets = (array)$request->new_synsets;
        
        $query = "DELETE FROM synsets WHERE id=".$lemma_id;
        $lemma_res = DB::select(DB::raw($query));

        foreach ($new_synsets as $count => $synset) {
            $synset = Synset::create(['lemma_id'=>$lemma_id,
                                      'synset'  =>$synset['synset'],
                                      'meaning_n'  =>$synset['meaning_n'],
                                      'meaning_text'=>$synset['meaning_text'],
                                     ]);
        }      
    
        return Redirect::to('/synset/')
            ->withSuccess('Word with synsets is added');        
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
        $synset_builder = Synset::where('lemma_id',(int)$id);
        $synsets = $synset_builder->orderBy('meaning_n')->get();
        if (!$synsets) {
            return Redirect::to('/synset/')
                           ->withError('The lemma with ID='.$id.' doesn\'t exist');                    
        }
        $lemma_values[$lemma_id] = Lemma::getNameByID($id);
        $new_meaning_n = $synset_builder->orderBy('meaning_n','desc')->select('meaning_n')->first()->meaning_n;
        return view('synset.update')
                  ->with(['new_meaning_n' => $new_meaning_n,
                          'lemma_id' => $id,
                          'synsets' => $synsets
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
