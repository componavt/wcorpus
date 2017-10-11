@extends('layouts.page')

@section('title')
Texts
@stop

@section('panel-heading')
Texts
@stop

@section('panel-body')

        <h2>
            @if (!$text->included) 
                <s>
            @endif        
            {{$text->title}}
            @if (!$text->included) 
                </s> (Text is excluded from research)
            @endif        
        </h2>
        
        <p>
            <a href="/text{{$args_by_get}}">Back to the text list</a>&nbsp;&nbsp;|&nbsp;
            <a href="/text/{{$text->id}}/include-exclude/{{$text->included ? 0 : 1}}?{{$args_by_get}}">
                {{$text->included ? 'Exclude from' : 'Include to'}} the research
                </a>&nbsp;&nbsp;|&nbsp;
            <a href="/text/{{$text->id}}/edit{{$args_by_get}}">Edit</a>&nbsp;&nbsp;|&nbsp;
            <a href="/text/{{$text->id}}/parseWikitext{{$args_by_get}}">Re-parse wikitext</a>&nbsp;&nbsp;|&nbsp;
            <a href="/text/{{$text->id}}/break_into_sentences{{$args_by_get}}">Break the text into sentences</a>            
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

