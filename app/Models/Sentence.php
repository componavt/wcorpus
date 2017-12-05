<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

use Wcorpus\Models\Wordform;

class Sentence extends Model
{
    protected $fillable = ['text_id','sentence','lemma_found','lemma_id'];
    public $timestamps = false;
    
    // Sentence __belongs_to__ Text
    public function text()
    {
        return $this->belongsTo(Text::class);
    }

    //  Sentence __has_many__ Wordforms
    public function wordforms(){
        $builder = $this->belongsToMany(Wordform::class,'sentence_wordform');
        return $builder;
    }

    public static function countByAuthor($author_id) {
        if (!$author_id) {
            return null;
        }
        $query = "SELECT count(*) as count FROM sentences WHERE text_id in "
               . "(select id FROM texts where author_id=".(int)$author_id.")";
//print '<p>'.$query;        
        $results = DB::select( DB::raw($query) );
        if (!$results) {
            return null;
        }
        return $results[0]->count;
        
    }

    public static function countByIDAndAuthor($id,$author_id) {
        if (!$id || !$author_id) {
            return null;
        }
        $results = DB::select( DB::raw("SELECT count(*) as count FROM sentences WHERE "
                . "text_id in (select id FROM texts where author_id=".(int)$author_id.") "
                 . "AND sentence_id=".(int)$id.")") );
        if (!$results) {
            return null;
        }
        return $results[0]->count;
        
    }
    
    /** delete all linked Wordforms
     */
    public function deleteWordforms() {
        $this->wordform_total = NULL;
        $this->save();
        
        if ($this->wordforms()->count()) {
            foreach ($this->wordforms as $wordform) {
                $this->wordforms()->detach($wordform->id);
                
                if (!$wordform->sentences()->wherePivot('sentence_id','<>',$this->id)->count()) { // this wordform links with only this sentence
                    $wordform->deleteLemmas();
                    $wordform->delete();
                } 
            }
        }
    }

    /** Get $sentence->sentence and break it into words,
     * and write words into wordforms table
     */
    public function breakIntoWords() {
        $this -> wordforms()->detach();
        
        $wordforms = self::splitIntoWords($this->sentence);
        if (sizeof($wordforms)>2) {
            $this->processWordforms($wordforms);            
        } else {
            $this->deleteFromText();
        }
    }

    /** 
     */
    public function processWordforms($wordforms) {
        $count=0;
        $only_with_basic_POS=1;
        $matrix = [];
        foreach ($wordforms as $wordform_count => $wordform) {
            if (mb_strlen($wordform)>45) {
                    $wordform = mb_substr($wordform,0,42).'...';
            }
            // save the wordform even without a lemma, so as not to re-lemmatize (чтобы повторно не лемматизировать, когда снова встетится та же словоформа)
            $wordform_obj = Wordform::firstOrCreate(['wordform' => $wordform]);
            $wordform_obj -> linkWithSentence($this->id,$wordform_count); 
            $lemmas = $wordform_obj -> getLemmaIDs($only_with_basic_POS);
            if ($lemmas) {
                $matrix[] = ['word_count'=>$wordform_count, 
                             'wordform_id'=>$wordform_obj->id, 
                             'lemmas'=>$lemmas];
            }
            $count++;
        }
        //$this->addToLemmaMatrix($matrix);
        $this->wordform_total = $count;
        $this->save();
    }

    /** 
     * creating lemma matrix from array $wordforms
     * with restriction: the distance between words is not more 2
     * @param Array $wordforms such as [ ['word_count'=>0, 'wordform_id'=>1, 'lemmas'=>[1,2],
     *                                ['word_count'=>2, 'wordform_id'=>5, 'lemmas'=>[3], ...   ]
     */
    public function addToLemmaMatrix($wordforms) {
        if (!sizeof($wordforms)) {
            return;
        }
        
        for ($i=1; $i<sizeof($wordforms); $i++) {
            // distance between words
            if ($wordforms[$i]['word_count'] - $wordforms[$i-1]['word_count'] >2) {
                continue;
            }
            $left_lemmas = $wordforms[$i-1]['lemmas'];
            $right_lemmas = $wordforms[$i]['lemmas'];

            foreach ($left_lemmas as $left_lemma_id) {
                foreach ($right_lemmas as $right_lemma_id) {
                    if ($left_lemma_id == $right_lemma_id) {
                        continue;
                    }
                    if ($left_lemma_id<$right_lemma_id) {
                        $count12=1;
                        $count21=0;
                        $lemma1 = $left_lemma_id;
                        $lemma2 = $right_lemma_id;
                    } else {
                        $count12=0;
                        $count21=1;
                        $lemma1 = $right_lemma_id;
                        $lemma2 = $left_lemma_id;
                    }
print "<P>".$wordforms[$i-1]['wordform_id']." - ".$wordforms[$i]['wordform_id']." = $lemma1 - $lemma2 = $count12 - $count21"; 
                    $pair = LemmaMatrix::firstOrCreate([
                            'lemma1'=>$lemma1, 
                            'lemma2'=>$lemma2 
                        ]);
                    $pair->freq_12 += $count12;
                    $pair->freq_21 += $count21;
                    $pair->save();
                }                            
            }
        }
        
    }

    /** Уменьшить на 1 счетчик предложений у текста и удалить предложение совсем
     */
    public function deleteFromText() {
        $text = Text::find($this->text_id);
        $text->sentence_total -=1;
        $text->save();
        $this->delete();
    }
        
    /**
     * Split a sentence into words without punctuation marks
     * extract only cyrillic letters and dash 
     *
     * @param $text String text 
     * @return Array collection of words
     */
    public static function splitIntoWords($text): Array
    {
        $words = [];
        $text = trim($text);

        if (!$text) {
            return $words;
        }
        // apostroph is needed in English only
//        if (preg_match_all("/(([[:alpha:]]+['-])*[[:alpha:]]+'?)/u",$text,$regs, PREG_PATTERN_ORDER)) {
//        if (preg_match_all("/(([[:alpha:]]+[-])*[[:alpha:]]+)/u",$text,$regs, PREG_PATTERN_ORDER)) {
//        if (preg_match_all("/(([А-Яа-яѢѣѲѳIiѴѵ]+[-])*[А-Яа-яѢѣѲѳIiѴѵ]+)/u",$text,$regs, PREG_PATTERN_ORDER)) {
        if (preg_match_all("/(([А-Яа-яё]+[-])*[А-Яа-яё]+)/u",$text,$regs, PREG_PATTERN_ORDER)) {
            foreach ($regs[0] as $word) {
               if (mb_strlen($word)>1) { // skip one-letter words
                   $words[] = $word;
               } 
            }
//var_dump($regs);            
        }

        return $words;
    }

    /**
     * Highlight a word
     *
     * @param String $wordform  
     * @return String sentence with highlighted words
     */
    public function highlightWordform(String $wordform): String
    {
        $sentence = $this->sentence;
        //$wordform_obj = Wordform::find($wordform_id);
        //$wordform = $wordform_obj->wordform;
        
        if ($wordform) {
            $sentence = preg_replace("/\b(".$wordform.")\b/ui","<span class=\"highlighted\">\\1</span>",$sentence);
        }
            
        return $sentence;
    }
    
    /**
     * Highlight all wordforms for $lemmas
     *
     * @param Array $lemmas  array with id of lemmas
     * @return String sentence with highlighted words
     */
    public function highlightLemmas(Array $lemmas): String
    {
        $sentence = $this->sentence;
        $wordforms = DB::table('sentence_wordform')
                       ->select(DB::raw('wordform_id'))
                       ->where('sentence_id',$this->id)
                       ->whereIn('wordform_id',function($query) use ($lemmas){
                                $query->select('wordform_id')
                                ->from('lemma_wordform')
                                ->whereIn('lemma_id', $lemmas);
                       })->get();
                
        foreach ($wordforms as $wordform) {
            $wordform_obj = Wordform::find($wordform->wordform_id);
            $sentence = preg_replace("/\b(".$wordform_obj->wordform.")\b/ui","<span class=\"highlighted\">\\1</span>",$sentence);
        }
            
        return $sentence;
    }
    
    public function toUtfLemmaList() {
        $query = "SELECT word_number,lemma_wordform.lemma_id as lemma_id FROM lemma_wordform, sentence_wordform where sentence_id=".$this->id
               . " and sentence_wordform.wordform_id=lemma_wordform.wordform_id ORDER BY word_number";
        $lemmas = [];
        $res = DB::select(DB::raw($query));
        foreach ($res as $row) {
            $lemma = Lemma::getLemmaWithPOSPosfix($row->lemma_id);
            $lemmas[] = "u'".$lemma."'";
        }
        $lemmas = array_unique($lemmas);
        return "[".join(", ", $lemmas)."]";
    }
}
