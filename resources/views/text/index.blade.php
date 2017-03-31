<?php $list_count = $url_args['limit_num'] * ($url_args['page']-1) + 1;?>
@extends('layouts.page')

@section('title')
List of texts
@stop

@section('panel-heading')
List of texts
@stop

@section('panel-body')
        {!! Form::open(['url' => '/text/',
                             'method' => 'get',
                             'class' => 'form-inline'])
        !!}
        @include('widgets.form._formitem_text',
                ['name' => 'search_title',
                'value' => $url_args['search_title'],
                'attributes'=>['size' => 15,
                               'placeholder'=>'Title']])
                               
        @include('widgets.form._formitem_select',
                ['name' => 'search_author',
                 'values' =>$author_values,
                 'value' =>$url_args['search_author'],
                 'attributes'=>['placeholder' => 'Author' ]])
        
        @include('widgets.form._formitem_btn_submit', ['title' => 'View'])

        show by
        @include('widgets.form._formitem_text',
                ['name' => 'limit_num',
                'value' => $url_args['limit_num'],
                'attributes'=>['size' => 5,
                               'placeholder' => 'Number of records' ]]) records
        {!! Form::close() !!}

        <p>Founded records: {{$numAll}}</p>

        @if ($texts)
        <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Title</th>
                <th>Author</th>
                <th>Publication</th>
                @if (Auth::check())
                <th></th>
                @endif
            </tr>
        </thead>
            @foreach($texts as $text)
            <tr>
                <td>{{ $list_count++ }}</td>
                <td><a href="text/{{$text->id}}{{$args_by_get}}">{{$text->title}}</a></td>
                <td>
                    @if($text->author)
                        {{$text->author->name}}
                    @endif
                </td>
                <td>
                    @if($text->publication)
                        {{$text->publication->title}}
                    @endif
                </td>
                @if (Auth::check())
                <td>
                    @include('widgets.form._button_edit', 
                             ['is_button'=>true, 
                              'route' => '/text/'.$text->id.'/edit',
                             ])
                </td>
                @endif
            </tr>
            @endforeach
        </table>
            {!! $texts->appends($url_args)->render() !!}
        @endif

@stop

