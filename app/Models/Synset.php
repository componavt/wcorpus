<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

use Wcorpus\Models\Lemma;

class Synset extends Model
{
    protected $fillable = ['lemma_id','synset','meaning_n','meaning_text'];
    public $timestamps = false;
    
    public function synsetToUtfList() {
      $list = preg_split('/\,\s*/',$this->synset);
      $posfix = Lemma::getLemmaPOSPosfix($this->lemma_id);
      for ($i=0; $i<sizeof($list); $i++) {
          $list[$i] .= $posfix; 
      }
      $list = array_unique($list);
      return "[u'".join("', u'", $list)."']";
    }
}
