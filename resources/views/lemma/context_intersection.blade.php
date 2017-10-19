<?php 
    $list_count = $url_args['limit_num'] * ($url_args['page']-1) + 1;
?>
@extends('layouts.page')

@section('title')
Lemma context intersection
@stop

@section('panel-heading')
Lemma context intersection
@stop

@section('headExtra')
    {!!Html::style('css/select2.min.css')!!}
    {!!Html::style('css/text.css')!!}
@stop

@section('panel-body')
        {!! Form::open(['url' => '/lemma/context_intersection/',
                             'method' => 'get',
                             'class' => 'form-inline'])
        !!}
            @include('widgets.form._formitem_select2',
                    ['name' => 'search_lemma1',
                     'class'=>'select-lemma1 form-control search-lemma',
                     'value' =>$url_args['search_lemma1'],
                     'values' => $lemma1_values,
                     'is_multiple' => false,
                     'attributes'=>['placeholder' => 'Lemma 1' ]])

            @include('widgets.form._formitem_select2',
                    ['name' => 'search_lemma2',
                     'class'=>'select-lemma2 form-control search-lemma',
                     'value' =>$url_args['search_lemma2'],
                     'values' => $lemma2_values,
                     'is_multiple' => false,
                     'attributes'=>['placeholder' => 'Lemma 2' ]])

            @include('widgets.form._formitem_btn_submit', ['title' => 'View'])
            
            by
            @include('widgets.form._formitem_text',
                    ['name' => 'limit_sentences',
                    'value' => $url_args['limit_sentences'],
                    'attributes'=>['size' => 5,
                    'placeholder' => 'Number of records' ]]) sentences,<span style='padding-right:20px'></span>
            by
            @include('widgets.form._formitem_text',
                    ['name' => 'limit_lemmas',
                    'value' => $url_args['limit_lemmas'],
                    'attributes'=>['size' => 5,
                                   'placeholder' => 'Number of records' ]]) lemmas
                 
        {!! Form::close() !!}
        <div class="row">
            <div class="col-sm-9">
            @include('lemma._sentence_list',[
                    'limit'=>$url_args['limit_sentences'],
                    'list' => $sentence_list])
            </div>
            <div class="col-sm-3">
            @include('lemma._context_lemma_intersection',[
                    'limit'=>$url_args['limit_lemmas'],
                    'list' => $lemma_list])
            </div>
        </div>
@stop

@section('footScriptExtra')
    {!!Html::script('js/select2.min.js')!!}
@stop

@section('jqueryFunc')
    $(".select-lemma1").select2({
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
    
    $(".select-lemma2").select2({
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
