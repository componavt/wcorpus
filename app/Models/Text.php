<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

class Text extends Model
{
    protected $fillable = ['wikitext','text_xml'];
}
