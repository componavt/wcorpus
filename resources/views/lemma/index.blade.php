<?php $list_count = $url_args['limit_num'] * ($url_args['page']-1) + 1;?>
@extends('layouts.page')

@section('title')
List of lemmas
@stop

@section('panel-heading')
List of lemmas
@stop

@section('panel-body')
        {!! Form::open(['url' => '/lemma/',
                             'method' => 'get',
                             'class' => 'form-inline'])
        !!}
        
        @include('widgets.form._formitem_text',
                ['name' => 'search_lemma',
                'value' => $url_args['search_lemma'],
                'attributes'=>['placeholder' => 'Wordform' ]]) 
        @include('widgets.form._formitem_btn_submit', ['title' => 'View'])

        by
        @include('widgets.form._formitem_text',
                ['name' => 'limit_num',
                'value' => $url_args['limit_num'],
                'attributes'=>['size' => 5,
                               'placeholder' => 'Number of records' ]]) records
        @include('widgets.form._formitem_hidden',
                ['name' => 'search_wordform',
                'value' => $url_args['search_wordform']]) 
        {!! Form::close() !!}

        <p>Founded records: {{$numAll}}</p>
        @if ($lemmas)
        <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Wordform</th>
                <th>Lemmas</th>
                <th style='text-align:center'>Number of wordforms</th>
                @if (Auth::check())
                <th colspan='2'></th>
                @endif
            </tr>
        </thead>
            @foreach($lemmas as $lemma)
            <?php $lemma_obj = \Wcorpus\Models\Wordform::find($lemma->id); ?>
            <tr>
                <td>{{ $list_count++ }}</td>
                <td>{{$lemma_obj->lemma}}</td>
                <td>
                    @if($lemma->lemmas())
                        @foreach ($lemma->lemmas as $lemma)
                            {{$lemma->lemma}}
                        @endforeach
                    @endif
                </td>
                <td style='text-align:center'>
                    @if($lemma->wordforms())
                        <a href="/wordform/?search_lemma={{$lemma_obj->id}}">{{$lemma->wordforms()->count()}}</a><br>
                    @endif
                </td>
                @if (Auth::check())
                <td>
                    @include('widgets.form._button', 
                                 ['is_button'=>true, 
                                  'route' => '/lemma/'.$lemma_obj->id.'/lemmatize',
                                  'title' => 'lemmatize',
                                  'with_args' => false
                                 ])
                </td>
                <td>
                    @include('widgets.form._button_delete', ['is_button'=>true, $route = 'lemma.destroy', 'id' => $lemma_obj->id])                                 
                </td>
                @endif
            </tr>
            @endforeach
        </table>
            {!! $lemmas->appends($url_args)->render() !!}
        @endif

@stop
