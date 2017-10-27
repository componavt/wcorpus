@extends('layouts.page')

@section('title')
Comparision of bigrams for authors
@stop

@section('panel-heading')
Comparision of bigrams for authors
@stop

@section('panel-body')
        {!! Form::open(['url' => '/bigram/',
                             'method' => 'get',
                             'class' => 'form-inline'])
        !!}
        @include('widgets.form._formitem_select',
                ['name' => 'search_author',
                 'value' =>$url_args['search_author'],
                 'values' => $author_values,
                 'attributes'=>['placeholder' => 'Author 1' ]])
        @include('widgets.form._formitem_select',
                ['name' => 'search_author2',
                 'value' =>$url_args['search_author2'],
                 'values' => $author_values,
                 'attributes'=>['placeholder' => 'Author 2' ]])
        @include('widgets.form._formitem_btn_submit', ['title' => 'Compare'])
        {!! Form::close() !!}
        
@stop

