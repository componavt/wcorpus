<?php $list_count = $url_args['limit_num'] * ($url_args['page']-1) + 1;?>
@extends('layouts.page')

@section('title')
Lemma context search
@stop

@section('panel-heading')
Lemma context search
@stop

@section('headExtra')
    {!!Html::style('css/select2.min.css')!!}
    {!!Html::style('css/text.css')!!}
@stop

@section('panel-body')
        {!! Form::open(['url' => '/lemma/search_context/',
                             'method' => 'get',
                             'class' => 'form-inline'])
        !!}
        @include('widgets.form._formitem_select2',
                ['name' => 'search_id',
                 'class'=>'select-lemma form-control search-lemma',
                 'value' =>$url_args['search_id'],
                 'values' => $lemma_values,
                 'is_multiple' => false,
                 'attributes'=>['placeholder' => 'Lemma' ]])
                 
        @include('widgets.form._formitem_btn_submit', ['title' => 'View'])
        {!! Form::close() !!}
        
        @if ($sentence_list) 
        <h3>Sentences</h3>
        <OL>
            @foreach ($sentence_list as $sentence) 
            <LI>{{$sentence['sentence']}} (<b>{{$sentence['wordforms']}}</b>)</LI>
            @endforeach
        </OL>
        @endif

@stop

@section('footScriptExtra')
    {!!Html::script('js/select2.min.js')!!}
@stop

@section('jqueryFunc')
    $(".select-lemma").select2({
        width: '300px',
        ajax: {
          url: "/lemma/list_with_pos",
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
