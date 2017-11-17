<?php $count =1;?>
@extends('layouts.page')

@section('title')
List of lemmas with synsets
@stop

@section('panel-heading')
List of lemmas with synsets
@stop

@section('panel-body')
        @if (Auth::check())
        <a href="/synset/create">Create new word with synsets</a>
        @endif 
        @if ($lemmas)
        <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Lemma</th>
                <th>POS</th>
                <th>Synsets</th>
                <th>Meaning</th>
                <th></th>
            </tr>
        </thead>
            @foreach($lemmas as $id => $synsets)
            <?php $lemma_obj = \Wcorpus\Models\Lemma::find($id); 
                  $rows = sizeof($synsets)>1 ? sizeof($synsets): 1;?>
            <tr>
                <td rowspan="{{$rows}}">{{$count++ }}</td>
                <td rowspan="{{$rows}}">{{$lemma_obj->lemma}}</td>
                <td rowspan="{{$rows}}">{{$lemma_obj->pos->name}}</td>
                @if (isset($synsets[0]))
                <td>{{$synsets[0]->meaning_n}}. {{$synsets[0]->synset}}</td>
                <td>{{$synsets[0]->meaning_text}}</td>
                @endif
                @if (Auth::check())
                <td rowspan="{{$rows}}" style="text-align:center; vertical-align:middle">
                    @include('widgets.form._button_edit', 
                             ['is_button'=>true, 
                              'route' => '/synset/'.$id.'/edit',
                             ])
                </td>
                @endif
            </tr>
            @for ($i=1; $i<sizeof($synsets); $i++)
            <tr>
                <td>{{$synsets[$i]->meaning_n}}. {{$synsets[$i]->synset}}</td>
                <td>{{$synsets[$i]->meaning_text}}</td>
            </tr>
            @endfor
            @endforeach
        </table>
        @endif

@stop
