@extends('layouts.page')

@section('title')
DB structure
@stop

@section('panel-heading')
DB structure
@stop

@section('panel-body')
    <h3>Tables:</h3>
    @foreach($tables as $tname => $count)
        {{$tname}}: {{$count}}<br>
    @endforeach
@stop

