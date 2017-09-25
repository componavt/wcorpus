<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

use Wcorpus\Models\Wordform;

class Sentence extends Model
{
    protected $fillable = ['text_id','sentence'];
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

    /** Get $sentence->sentence and break it into words,
     * and write words into wordforms table
     */
    public function breakIntoWords() {
        $sentence = $this;
        
        $sentence->wordform_total = 0;
                
        $wordforms = self::splitIntoWords($sentence->sentence);
        $total = sizeof($wordforms);
        if ($total>2) {
            foreach ($wordforms as $wordform_count => $wordform) {
                  if (mb_strlen($wordform)>45) {
                        $wordform = mb_substr($wordform,0,42).'...';
                    }
    //print "<p>$wordform</p>";            
                $wordform_obj = Wordform::firstOrCreate(['wordform' => $wordform]);
                $wordform_obj->sentences()->attach($sentence->id,['word_number' => $wordform_count]);            
            }
            $sentence->wordform_total = $total;

            $sentence->save();
        } else {
            $sentence->texts()->detach();
            $sentence->delete();
        }
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
        if (preg_match_all("/(([А-Яа-я]+[-])*[А-Яа-я]+)/u",$text,$regs, PREG_PATTERN_ORDER)) {
            foreach ($regs[0] as $word) {
               if (mb_strlen($word)>1) {
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
     * @param String $wordform_id  
     * @return String sentence with highlighted words
     */
    public function highlightSentence(String $wordform): String
    {
        $sentence = $this->sentence;
        //$wordform_obj = Wordform::find($wordform_id);
        //$wordform = $wordform_obj->wordform;
        
        if ($wordform) {
            $sentence = preg_replace("/\b(".$wordform.")\b/ui","<span class=\"highlighted\">\\1</span>",$sentence);
        }
            
        return $sentence;
    }
    
}
