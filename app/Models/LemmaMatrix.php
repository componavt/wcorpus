<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

use Wcorpus\Wcorpus;
use Wcorpus\Models\Piwidict\LangPOS;

class LemmaMatrix extends Model
{
    protected $connection = 'mysql';
    protected $fillable = ['lemma1','lemma2'];
    protected $table = 'lemma_matrix';
    
    public $timestamps = false;


}
