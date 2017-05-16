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
                               
        @include('widgets.form._formitem_text',
                ['name' => 'search_wikitext',
                'value' => $url_args['search_wikitext'],
                'attributes'=>['size' => 15,
                               'placeholder'=>'Wikitext']])
                               
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
                <th>Date</th>
                <th>Wikitext length</th>
                <th>Parsed text length</th>
                <th>Sentences</th>
                @if (Auth::check())
                <th></th>
                @endif
            </tr>
        </thead>
            @foreach($texts as $text)
            <?php $text_obj = \Wcorpus\Models\Text::find($text->id); ?>
            <tr>
                <td>{{ $list_count++ }}</td>
                <td><a href="text/{{$text->id}}{{$args_by_get}}">{{$text_obj->title}}</a></td>
                <td>
                    @if($text_obj->author)
                        {{$text_obj->author->name}}
                    @endif
                </td>
                <td>
                    @if($text_obj->publication)
                        {{$text_obj->publication->title}}
                    @endif
                </td>
                <td>
                    @if($text_obj->publication)
                        {{$text_obj->publication->creation_date}}
                    @endif
                </td>
                <td>
                    @if($text_obj->wikitext)
                        {{strlen($text_obj->wikitext)}}
                    @endif
                </td>
                <td>
                    @if($text_obj->text)
                        {{strlen($text_obj->text)}}
                    @endif
                </td>
                <td>
                    @if($text_obj->sentence_total)
                        {{$text_obj->sentence_total}}
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

