<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

class Sentence extends Model
{
    protected $fillable = ['text_id','sentence'];
    public $timestamps = false;
    

    
}
