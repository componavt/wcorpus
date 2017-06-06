<?php $list_count = 1;?>
@extends('layouts.page')

@section('title')
List of relation types
@stop

@section('panel-heading')
List of relation types
@stop

@section('panel-body')
        @if ($rel_types)
        <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Name</th>
            </tr>
        </thead>
            @foreach($rel_types as $rel_type)
            <tr>
                <td>{{ $list_count++ }}</td>
                <td>{{$rel_type->name}}</td>
            </tr>
            @endforeach
        </table>
        @endif

@stop
