<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

class Synset extends Model
{
    protected $fillable = ['lemma_id','synset','meaning_n','meaning_text'];
    public $timestamps = false;
}
