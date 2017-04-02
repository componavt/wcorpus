@extends('layouts.page')

@section('title')
Texts
@stop

@section('panel-heading')
Texts
@stop

@section('panel-body')

        <h2>{{$text->title}}</h2>
        
        @if($text->author)
        <p><b>Author:</b> {{$text->author->name}}    
        @endif

        <pre><?php print  str_replace('{','\{',$text->wikitext);
        // print  str_replace('{','\{',str_replace('}','\}',$text->wikitext));
        // print  str_replace('{','&#'.ord('{').';',$text->wikitext);
        // print  str_replace('{','&#'.ord('{'),str_replace('}','&#'.ord('}'),$text->wikitext)); ?>
        </pre>
@stop

