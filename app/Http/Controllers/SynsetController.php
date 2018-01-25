<?php

namespace Wcorpus\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Redirect;
use Storage;

use Wcorpus\Models\Lemma;
use Wcorpus\Models\Sentence;
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
        $query = "SELECT DISTINCT lemma_id, lemma FROM synsets, lemmas where lemmas.id=lemma_id order by lemma";
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
        
/*        $query = "DELETE FROM synsets WHERE id=".$lemma_id;
        $lemma_res = DB::select(DB::raw($query)); */

        foreach ($new_synsets as $count => $synset) {
            $synset = Synset::create(['lemma_id'=>$lemma_id,
                                      'synset'  =>$synset['synset'],
                                      'meaning_n'  =>$synset['meaning_n'],
                                      'meaning_text'=>$synset['meaning_text'],
                                     ]);
        }      
    
        return Redirect::to('/synset/')
            ->withSuccess('Synsets of the lemma "'.Lemma::getLemmaByID($lemma_id). '"  are added');        
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
        $synsets = Synset::where('lemma_id',(int)$id)
                        ->orderBy('meaning_n')->get();
        if (!$synsets) {
            return Redirect::to('/synset/')
                           ->withError('The synsets with with lemma ID='.$id.' doesn\'t exist');                    
        }
        $lemma_values[$id] = Lemma::getLemmaByID($id);
        $new_meaning_n = 1 + (int)Synset::where('lemma_id',(int)$id)
                       ->orderBy('meaning_n','desc')
                       ->select('meaning_n')->first()->meaning_n;
        return view('synset.edit')
                  ->with(['new_meaning_n' => $new_meaning_n,
                          'lemma_id' => $id,
                          'lemma_values' => $lemma_values,
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
        $this->validate($request, [
            'lemma_id' => 'required|max:255',
        ]);
        $lemma_id = (int)$request->lemma_id;
        $synsets = Synset::where('lemma_id',$lemma_id)->get();
        if (!$synsets) {
            return Redirect::to('/synset/')
                           ->withError('The synsets with with lemma ID='.$id.' doesn\'t exist');                    
        }
        foreach ((array)$request->synsets as $synset_id => $synset) {
            $synset_obj = Synset::findOrFail($synset_id);
            $synset_obj->lemma_id = $lemma_id;
            $synset_obj->synset = $synset['synset'];
            $synset_obj->meaning_n =$synset['meaning_n'];
            $synset_obj->meaning_text =$synset['meaning_text'];
            $synset_obj->save();
            
        }
       
        foreach ((array)$request->new_synsets as $count => $synset) {
            $synset = Synset::create(['lemma_id'=>$lemma_id,
                                      'synset'  =>$synset['synset'],
                                      'meaning_n'  =>$synset['meaning_n'],
                                      'meaning_text'=>$synset['meaning_text'],
                                     ]);
        }      
    
        return Redirect::to('/synset/')
            ->withSuccess('Synsets of the lemma "'.Lemma::getLemmaByID($lemma_id). '" are updated');        
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
     * Search form for assigning lemma synsets to sentences
     */
    public function sentencesSearch()
    {
        $query = "SELECT DISTINCT lemma_id, lemma FROM synsets, lemmas where lemmas.id=lemma_id order by lemma";
        $lemma_res = DB::select(DB::raw($query));
//        $lemma_values[''] = 'Choose lemma';
        $lemma_values = [];
        foreach ($lemma_res as $l) {
            $lemma_values[$l->lemma_id] = $l->lemma;
        }
        return view('synset.sentences_search')
                  ->with([
                          'lemma_values' => $lemma_values,
                         ]);
    }
    
    /**
     * Assign lemma synsets to sentences
     */
    public function sentencesEdit(Request $request)
    {
        $lemma_id = (int)$request->lemma_id;
      
        $lemma = Lemma::find($lemma_id);
        
        if (!$lemma) {
            return Redirect::to('/synset/sentences');
        }

        if (isset($request->sentence_synset) && is_array($request->sentence_synset)) {
            foreach ($request->sentence_synset as $sentence_id => $synset_id) {
                if ($synset_id != '') {
                    $query = "SELECT synset_id FROM lemma_sentence_synset where lemma_id=$lemma_id and sentence_id = ".$sentence_id;
                    $res = DB::select(DB::raw($query));
                    if (!isset($res[0])) {
                        $query = "INSERT INTO  lemma_sentence_synset (lemma_id, sentence_id, synset_id) VALUES ($lemma_id, $sentence_id, $synset_id)";
                        $res = DB::select(DB::raw($query));
                    } elseif ($res[0] != $synset_id) {
                        $query = "UPDATE lemma_sentence_synset SET synset_id=".(int)$synset_id." where lemma_id=$lemma_id and sentence_id = ".$sentence_id;
                        $res = DB::select(DB::raw($query));
                    }
                }
            }
        }

        $synset_values = [];
        $synset_sentences[NULL] = [0,[]];
//        $synset_values[''] = ['Choose synset'];
        
        if ($lemma_id) {
            $synsets = Synset::where('lemma_id',(int)$lemma_id)
                            ->orderBy('meaning_n')->get();
            foreach ($synsets as $synset) {
                $synset_values[$synset->id] 
                        = $synset->meaning_n. '. '. $synset->synset 
                        .' ('. $synset->meaning_text.')';
                $synset_sentences[$synset->id] = [$synset->meaning_n,[]];
            }
            
            $synset_values[0] = '∞. REMOVE FROM CONSIDERATION';
            
            $sentences = Sentence::orderBy('text_id')
                    ->whereIn('id',function($query) use ($lemma_id){
                                $query->select('sentence_id')
                                ->from('sentence_wordform')
                                ->whereIn('wordform_id',function($query) use ($lemma_id){
                                    $query->select('wordform_id')
                                    ->from('lemma_wordform')
                                    ->where('lemma_id', $lemma_id);
                                });
                            })->get();
            foreach ($sentences as $sentence) {
                $query = "SELECT synset_id FROM lemma_sentence_synset where lemma_id=$lemma_id and sentence_id = ".$sentence->id;
                $res = DB::select(DB::raw($query));
                if (!isset($res[0])) {
                    $synset_sentences[NULL][1][] = $sentence;
                } elseif($res[0]->synset_id > 0) {
//                } else {
                    $synset_sentences[$res[0]->synset_id][1][] = $sentence;
                }
//                $sentence->synset = $res ? $res[0]->synset_id : NULL;
            }      
//            $sentences = $sentences->sortBy('synset');
//dd($sentences);            
        } else {
            $sentences = [];
        }
//dd($synset_sentences);        
        return view('synset.sentences_edit')
                  ->with([
                          'lemma' => $lemma,
                          'synset_values' => $synset_values,
                          'synset_sentences' => $synset_sentences
                         ]);
    }
    
    /**
     * Create 2 python files:
     * synsets.py with code:
     *  synsets = {
     *  }
     * sentences.py
     */
    public function downloadForPython()
    {
        $query = "SELECT DISTINCT lemma_id, lemma FROM synsets, lemmas where lemmas.id=lemma_id order by lemma"; #limit 10
        $lemma_res = DB::select(DB::raw($query));
        $lemmas = [];
        $sentences = [];
        foreach ($lemma_res as $lemma) {
            $synset_lines = [];
            $synsets = Synset::where('lemma_id',(int)$lemma->lemma_id)
                            ->orderBy('meaning_n')->get();
            foreach ($synsets as $synset) {
                $synset_lines[] = "\t\t".$synset->id. ": ". $synset->synsetToUtfList() ."";
            }
            $lemmas[] = "\tu'".$lemma->lemma."': {\n".join(",\n",$synset_lines)."\n\t}";
            
            $query = "SELECT sentence_id, synset_id FROM lemma_sentence_synset where synset_id>0 and lemma_id=".(int)$lemma->lemma_id;#." limit 1";
            $sentences_res = DB::select(DB::raw($query));
            
            foreach ($sentences_res as $sentence) {
                $sentence_obj = Sentence::find($sentence->sentence_id);
                $sentences[] = "\tu'".str_replace("'",'&#39;',$sentence_obj->sentence)."': {\n"
                             . "\t\t'lemmas': ".$sentence_obj->toUtfLemmaList().",\n"
                             . "\t\t'lemma': u'".$lemma->lemma."',\n"
                             . "\t\t'synset_exp': ".$sentence->synset_id.",\n"
                             . "\t\t'synset_alg1': '',\n\t\t'synset_alg2': '',\n\t\t'synset_alg3': '',\n"
                             . "\t\t'alg1_right': '',\n\t\t'alg2_right': '',\n\t\t'alg3_right': ''\n\t}";
            }
        }
        
        $synset_lines = "#!/usr/bin/env python\n"
                      . "# -*- coding: utf-8 -*-\n"
                      . "synsets = {\n"
                      . join(",\n",$lemmas)
                      . "\n}\n";
        Storage::disk('public')->put('synsets.py', $synset_lines);
        print '<p><a href="'.asset('storage/synsets.py').'">synsets.py</a></p>';
        
        $sentence_lines = "#!/usr/bin/env python\n"
                      . "# -*- coding: utf-8 -*-\n"
                      . "sentences = {\n"
                      . join(",\n",$sentences)
                      . "\n}\n";
        Storage::disk('public')->put('sentences_in.py', $sentence_lines);
        print '<p><a href="'.asset('storage/sentences_in.py').'">sentences_in.py</a></p>';
    }
}
# select synset_id,lemma_id, count(*) as count from lemma_sentence_synset where synset_id>0 group by synset_id, lemma_id order by synset_id;
# select * from lemmas where id = 1060683;
# delete from lemma_sentence_synset where synset_id in (7,8,9,10) and lemma_id <> 1061428;
# delete from lemma_sentence_synset where synset_id in (14,15) and lemma_id <> 1064659;