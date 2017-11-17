<?php $count =1;?>
@extends('layouts.page')

@section('title')
Edition of synsets
@stop

@section('panel-heading')
Edition of synsets
@stop

@section('headExtra')
    {!!Html::style('css/select2.min.css')!!}
@stop

@section('panel-body')
        <a href="/synset">Back to the list</a>
        {!! Form::open(array('method'=>'PUT', 'route' => ['synset.update', $lemma_id])) !!}
        @include('synset._form_create_edit', ['submit_title' => "SAVE",
                                      'action' => 'update'])
        {!! Form::close() !!}
@stop

@section('footScriptExtra')
    {!!Html::script('js/add_fields.js')!!}
    {!!Html::script('js/select2.min.js')!!}
@stop

@section('jqueryFunc')
    addSynset();
    
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
