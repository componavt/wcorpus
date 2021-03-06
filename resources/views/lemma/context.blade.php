<?php 
    $list_count = $url_args['limit_num'] * ($url_args['page']-1) + 1;
?>
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
        <div class="row">
            <div class="col-sm-4">
            @include('widgets.form._formitem_select2',
                    ['name' => 'search_id',
                     'class'=>'select-lemma form-control search-lemma',
                     'value' =>$url_args['search_id'],
                     'values' => $lemma_values,
                     'is_multiple' => false,
                     'attributes'=>['placeholder' => 'Lemma' ]])
            </div>     
            <div class="col-sm-1">
            @include('widgets.form._formitem_btn_submit', ['title' => 'View'])
            </div>     
            <div class="col-sm-4">
            
            by
            @include('widgets.form._formitem_text',
                    ['name' => 'limit_sentences',
                    'value' => $url_args['limit_sentences'],
                    'attributes'=>['size' => 5,
                                   'placeholder' => 'Number of records' ]]) sentences
            </div>     
            <div class="col-sm-3">
            by
            @include('widgets.form._formitem_text',
                    ['name' => 'limit_lemmas',
                    'value' => $url_args['limit_lemmas'],
                    'attributes'=>['size' => 5,
                                   'placeholder' => 'Number of records' ]]) lemmas
                 
            </div>     
        </div>
        {!! Form::close() !!}
        <div class="row">
            <div class="col-sm-9">
            @include('lemma._sentence_list',[
                    'limit'=>$url_args['limit_sentences'],
                    'list' => $sentence_list])
            </div>
            <div class="col-sm-3">
            @include('lemma._lemma_list',[
                    'limit'=>$url_args['limit_lemmas'],
                    'list' => $context_lemmas])
            </div>
        </div>
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
