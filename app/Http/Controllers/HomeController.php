<?php

namespace Wcorpus\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use Wcorpus\Models\Sentence;
use Wcorpus\Models\Text;
use Wcorpus\Models\Wordform;

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
        return view('home'); //->with(['text'=>$page->page_content_model])
    }

    public function stats()
    {
        $results = DB::select( DB::raw("SELECT count(*) as count FROM texts") );
        $stats['text_total'] = number_format($results[0]->count, 0, '.', ' ');

        $results = DB::select( DB::raw("SELECT count(*) as count FROM texts WHERE included=1") );
        $stats['text_included'] = number_format($results[0]->count, 0, '.', ' ');

        $results = DB::select( DB::raw("SELECT count(*) as count FROM sentences") );
        $stats['sentence_total'] = number_format($results[0]->count, 0, '.', ' ');

        $results = DB::select( DB::raw("SELECT count(*) as count FROM wordforms") );
        $wordform_total = $results[0]->count;
        $stats['wordform_total'] = number_format($wordform_total, 0, '.', ' ');

        $results = DB::select( DB::raw("SELECT count(*) as count FROM wordforms "
                . "where id in (select wordform_id from lemma_wordform,lemmas "
                . "where lemma_id=lemmas.id and dictionary=0)") );
        $predicted_wordform_total = $results[0]->count;
        $stats['predicted_wordform_total'] = number_format($predicted_wordform_total, 0, '.', ' ');

        $results = DB::select( DB::raw("SELECT count(*) as count FROM wordforms "
                . " where lemma_total=0") );
        $wordform0_total = $results[0]->count;
        $stats['wordform0_total'] = number_format($wordform0_total, 0, '.', ' ');
        
        $stats['wordform_clear_total'] = number_format($wordform_total - $wordform0_total - $predicted_wordform_total, 0, '.', ' ');

        $results = DB::select( DB::raw("SELECT count(*) as count FROM lemmas") );
        $lemma_total = $results[0]->count;
        $stats['lemma_total'] = number_format($lemma_total, 0, '.', ' ');

        $results = DB::select( DB::raw("SELECT count(*) as count FROM lemmas where dictionary=0") );
        $lemma_predicted_total = $results[0]->count;
        $stats['lemma_predicted_total'] = number_format($lemma_predicted_total, 0, '.', ' ');

        $stats['lemma_clear_total'] = number_format($lemma_total - $lemma_predicted_total, 0, '.', ' ');
        
        $table_names = DB::select(DB::raw("SHOW TABLES"));
        $tables = [];
        foreach($table_names as $table_name) {
            $tname = $table_name->Tables_in_wcorpus;
            $counts = DB::select( DB::raw("SELECT count(*) as count FROM ".$tname) );
            $tables[$tname] = number_format($counts[0]->count, 0, '.', ' ');            
        }

        return view('stats')->
                with(['stats'=>$stats,
                      'tables' =>$tables]); 
    }
}
