<?php $count =1;?>
@extends('layouts.page')

@section('title')
List of lemmas with synsets
@stop

@section('panel-heading')
List of lemmas with synsets
@stop

@section('panel-body')
{{--        @if (\Wcorpus\User::checkAccess('auth')) --}}
        <a href="/synset/create">Create new word with synsets</a>
{{--        @endif --}}
        @if ($lemmas)
        <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Lemma</th>
                <th>POS</th>
                <th>Synsets</th>
                <th>Meaning</th>
            </tr>
        </thead>
            @foreach($lemmas as $id => $synsets)
            <?php $lemma_obj = \Wcorpus\Models\Lemma::find($id); ?>
            <tr>
                <td>{{$count++ }}</td>
                <td>{{$lemma_obj->lemma}}</td>
                <td>{{$lemma_obj->pos->name}}</td>
                @foreach ($synsets as $synset)
                <td>{{$synset->meaning_n}}. {{$synset->synset}}</td>
                <td>{{$synset->meaning_text}}</td>
                @endforeach
            </tr>
            @endforeach
        </table>
        @endif

@stop
