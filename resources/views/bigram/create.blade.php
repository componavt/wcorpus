@extends('layouts.page')

@section('title')
Creating of bigrams for an author
@stop

@section('panel-heading')
Creating of bigrams for an author
@stop

@section('headExtra')
    {!!Html::style('css/select2.min.css')!!}
@stop

@section('panel-body')
        {!! Form::open(['url' => '/bigram/create/',
                             'method' => 'get',
                             'class' => 'form-inline'])
        !!}
        @include('widgets.form._formitem_select2',
                ['name' => 'search_author',
                 'class'=>'select-author form-control search-author',
                 'value' =>$url_args['search_author'],
                 'values' => $author_values,
                 'is_multiple' => false,
                 'attributes'=>['placeholder' => 'Author' ]])
        @include('widgets.form._formitem_btn_submit', ['title' => 'Create'])
        {!! Form::close() !!}
        
@stop

@section('footScriptExtra')
    {!!Html::script('js/select2.min.js')!!}
@stop

@section('jqueryFunc')
    $(".select-author").select2({
        width: '300px',
        ajax: {
          url: "/author/name_list",
          dataType: 'json',
          delay: 250,
          data: function (params) {
            return {
              q: params.term // search term
            };
          },
          processResults: function (data) {
            return {
              results: data
            };
          },          
          cache: true
        }
    });
    
@stop
