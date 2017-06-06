<?php

namespace Wcorpus\Models\Piwidict;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $connection = 'ru_wikt';
    protected $table = 'page';
}
