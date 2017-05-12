@extends('layouts.page')
@section('title')
Texts
@stop

@section('panel-heading')
Text edition
@stop

@section('panel-body')

        <h2>{{$text->title}}</h2>
        <p><a href="/text/{{ $text->id }}">Back to view</a></p>
        
        {!! Form::model($text, array('method'=>'PUT', 'route' => array('text.update', $text->id))) !!}

        @include('widgets.form._url_args_by_post',['url_args'=>$url_args])
        
        @include('widgets.form._formitem_textarea', 
                ['name' => 'wikitext', 
                 'title'=> 'Wikitext',
                 'value' => str_replace("{","\{",$text->wikitext)
                ])

        @include('widgets.form._formitem_btn_submit', ['title' => 'save'])
        {!! Form::close() !!}
@stop

