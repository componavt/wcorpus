<?php $list_count = 1;?>
@extends('layouts.page')

@section('title')
Assign lemma synsets to sentences
@stop

@section('panel-heading')
Assign lemma synsets to sentences
@stop

@section('panel-body')
        {!! Form::open(['url' => '/synset/sentences/',
                             'method' => 'post',
                             'class' => 'form-inline'])
        !!}
        @include('widgets.form._formitem_select',
                ['name' => 'lemma_id',
                 'values' => $lemma_values,
                 'attributes'=>['placeholder' => 'Choose lemma' ]])
        @include('widgets.form._formitem_btn_submit', ['title' => 'Search lemma'])
        
        {!! Form::close() !!}
@stop

