<?php

namespace Wcorpus\Http\Controllers;

use Illuminate\Http\Request;

use Wcorpus\Models\LemmaMatrix;
use Wcorpus\Wcorpus;

class LemmaMatrixController extends Controller
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
        $this->url_args = [
                    'limit_num'       => (int)$request->input('limit_num'),
                    'page'            => (int)$request->input('page'),
//                    'search_wordform'  => (int)$request->input('search_sentence'),
                    'search_lemma'  => $request->input('search_wordform'),
                    'order_by'      => $request->input('order_by'),
                ];
        
        if (!$this->url_args['page']) {
            $this->url_args['page'] = 1;
        }
        
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
        if (!$this->url_args['order_by']) {
            $this->url_args['order_by'] = 'freq_12';
        }
        
        $matrix = LemmaMatrix::
//                select('id')->
                orderBy($this->url_args['order_by'], 'desc');

/*        if ($this->url_args['search_wordform']) {
            $wordform_id = $this->url_args['search_wordform'];
        
            $lemmas = $wordforms->whereIn('id',function($query) use ($wordform_id){
                                $query->select('wordform_id')
                                ->from('lemma_wordform')
                                ->where('wordform_id', $wordform_id);
                            });                    
        } 
        
        if ($this->url_args['search_lemma']) {
            $lemmas = $lemmas->where('lemma','like',$this->url_args['search_lemma']);
        } */
        
        $numAll = $matrix->get()->count();


        $matrix = $matrix
                ->paginate($this->url_args['limit_num']);         
        
        
            return view('lemma_matrix.index')
                  ->with(array(
                               'numAll'        => $numAll,
                               'matrix'        => $matrix,
                               'args_by_get'   => $this->args_by_get,
                               'url_args'      => $this->url_args,
                              )
                        );
    }
}
