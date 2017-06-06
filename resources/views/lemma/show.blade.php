@extends('layouts.page')

@section('title')
Lemmas
@stop

@section('panel-heading')
Lemmas
@stop

@section('panel-body')

        <h2>{{$lemma->lemma}}</h2>
        
        <p>
            <a href="/lemma/{{$text->id}}/edit">Edit</a>&nbsp;&nbsp;|&nbsp;
        </p>
        
        <p>
            <a href="/wordform/?search_lemma={{$text->id}}">Wordforms</a> ({{$lemma->wordforms()->count()}})
        </p>
        
        @if($lemma->pos && $lemma->pos->name)
        <p><b>Part of speech:</b> {{$lemma->pos->name}}    
        @endif

        @if($grams)
        <p><b>Grammems:</b>  {{join(', ',$grams)}}
        @endif
        
@stop

