<?php $list_count = 1;?>
@extends('layouts.page')

@section('title')
List of parts of speech
@stop

@section('panel-heading')
List of parts of speech
@stop

@section('panel-body')
        @if ($poses)
        <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Name</th>
                <th>AOT name</th>
                <th style='text-align:center'>Number of lemmas</th>
                @if (Auth::check())
                <th></th>
                @endif
            </tr>
        </thead>
            @foreach($poses as $pos)
            <tr>
                <td>{{ $list_count++ }}</td>
                <td>{{$pos->name}}</td>
                <td>{{$pos->aot_name}}</td>
                <td style='text-align:center'>{{$pos->lemmas()->count()}}</td>
                @if (Auth::check())
                <td>
                    @include('widgets.form._button_edit', 
                             ['is_button'=>true, 
                              'route' => '/pos/'.$pos->id.'/edit',
                             ])
                </td>
                @endif
            </tr>
            @endforeach
        </table>
        @endif

@stop
