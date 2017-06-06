@extends('layouts.page')

@section('title')
List of parts of speech
@stop

@section('panel-heading')
List of parts of speech
@stop

@section('panel-body')
        <h2>Part of speech "{{ $pos->name}}"</h2>
        <p><a href="/pos/">Back to list</a></p>
        
        {!! Form::model($pos, array('method'=>'PUT', 'route' => array('pos.update', $pos->id))) !!}
        @include('pos._form_create_edit', ['submit_title' => 'save',
                                      'action' => 'edit'])
        {!! Form::close() !!}
@stop