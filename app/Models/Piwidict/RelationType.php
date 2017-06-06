<?php

namespace Wcorpus\Models\Piwidict;

use Illuminate\Database\Eloquent\Model;

class RelationType extends Model
{
    protected $connection = 'ru_wikt';
    protected $table = 'relation_type';

}
