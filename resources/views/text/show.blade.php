@extends('layouts.page')

@section('title')
Texts
@stop

@section('panel-heading')
Texts
@stop

@section('panel-body')

        <h2>{{$text->title}}</h2>
        
        <p>
            <a href="/text/{{$text->id}}/edit">Edit</a>&nbsp;&nbsp;|&nbsp;
            <a href="/text/{{$text->id}}/parseWikitext">Re-parse wikitext</a>&nbsp;&nbsp;|&nbsp;
            <a href="/text/{{$text->id}}/break_into_sentences">Break the text into sentences</a>            
        </p>
        
        <p>
            <a href="/sentence/?search_text={{$text->id}}">Sentences</a> ({{$text->sentence_total}})
        </p>
        
        @if($text->publication && $text->publication->title)
        <p><b>Publication title:</b> {{$text->publication->title}}    
        @endif

        @if($text->publication && $text->publication->creation_date)
        <p><b>Creation date:</b> {{$text->publication->creation_date}}    
        @endif

        @if($text->author)
        <p><b>Author:</b> {{$text->author->name}}    
        @endif

        @if($text->text)
        <h3>Parsed text:</h3> 
        <pre>{{str_replace('{','\{',$text->text)}}</pre>
        @endif

        <h3>Text from wikisource:</h3>
        <pre>{{str_replace('{','\{',$text->wikitext)}}
<?php   // print  str_replace('{','\{',str_replace('}','\}',$text->wikitext));
        // print  str_replace('{','&#'.ord('{').';',$text->wikitext);
        // print  str_replace('{','&#'.ord('{'),str_replace('}','&#'.ord('}'),$text->wikitext)); ?>
        </pre>
@stop

