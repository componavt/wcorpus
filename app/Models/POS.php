<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

class POS extends Model
{
    protected $table = 'pos';
    protected $fillable = ['name', 'aot_name'];
  
    public $timestamps = false;
    
    // POS __has_many__ Lemmas
    public function lemmas()
    {
        return $this->hasMany(Lemma::class,'pos_id');
    }
    
}
