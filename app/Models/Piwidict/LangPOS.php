<?php

namespace Wcorpus\Models\Piwidict;

use Illuminate\Database\Eloquent\Model;

use Wcorpus\Models\Lemma;
use Wcorpus\Models\Piwidict\PartOfSpeech;
use Wcorpus\Models\Piwidict\Piwidict;


class LangPOS extends Model
{
    protected $connection = 'ru_wikt';
    protected $table = 'lang_pos';
    
    
    /**
     * 
     * @param Lemma $lemma
     * @return Array collection of LangPOS objects
     */
    public static function getByLemma($lemma_obj) {
        $lemma_pos = $lemma_obj->pos->name;
        $lemma = $lemma_obj->lemma;
        $pos_id = PartOfSpeech::getIDByName($lemma_pos);
//return NULL;

        $lang_pos = self::where("lang_id",Piwidict::lang())
                        ->whereIn('page_id',function($query) use ($lemma){
                                $query->select('id')
                                ->from('page')
                                ->where('page_title', 'like', $lemma);
                            });                    
        
        if ($pos_id) {
            $lang_pos = $lang_pos->where('pos_id',$pos_id);
        }
        
        return $lang_pos->get();
    }
}
