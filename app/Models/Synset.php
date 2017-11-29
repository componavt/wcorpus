<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

class Synset extends Model
{
    protected $fillable = ['lemma_id','synset','meaning_n','meaning_text'];
    public $timestamps = false;
    
    public function synsetToUtfList() {
      $list = preg_split('/\,\s*/',$this->synset);
      return "[u'".join("', u'", $list)."']";
    }
}
