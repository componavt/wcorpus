<?php

namespace Wcorpus\Http\Controllers\Piwidict;

use Illuminate\Http\Request;
use Wcorpus\Http\Controllers\Controller;

use Wcorpus\Models\Piwidict\RelationType;

class RelationTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rel_types = RelationType::orderBy('name')->get();
        return view('piwidict.relation_type.index')
                  ->with(['rel_types' => $rel_types]);
    }

}
