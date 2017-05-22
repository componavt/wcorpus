<?php

namespace Wcorpus\Http\Controllers;

use Illuminate\Http\Request;
use Response;

use Wcorpus\Models\Author;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    /**
     * Gets list of authors for drop down list in JSON format
     * Test url: /author/name_list
     * 
     * @return JSON response
     */
    public function namesList(Request $request)
    {
        $search_name = '%'.$request->input('q').'%';

        $list = [];
        $texts = Author::where('name','like', $search_name)
                       ->orderBy('name')->get();
        foreach ($texts as $text) {
            $list[]=['id'  => $text->id, 
                           'text'=> $text->name];
        }  

        return Response::json($list);
    }

}
