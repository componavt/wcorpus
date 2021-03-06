<?php $list_count = $url_args['limit_num'] * ($url_args['page']-1) + 1;?>
@extends('layouts.page')

@section('title')
List of wordforms
@stop

@section('panel-heading')
List of wordforms
@stop

@section('panel-body')
        {!! Form::open(['url' => '/wordform/',
                             'method' => 'get',
                             'class' => 'form-inline'])
        !!}
        
        @include('widgets.form._formitem_text',
                ['name' => 'search_wordform',
                'value' => $url_args['search_wordform'],
                'attributes'=>['placeholder' => 'Wordform' ]]) 
        @include('widgets.form._formitem_btn_submit', ['title' => 'View'])

        by
        @include('widgets.form._formitem_text',
                ['name' => 'limit_num',
                'value' => $url_args['limit_num'],
                'attributes'=>['size' => 5,
                               'placeholder' => 'Number of records' ]]) records
        @include('widgets.form._formitem_hidden',
                ['name' => 'search_sentence',
                'value' => $url_args['search_sentence']]) 
        {!! Form::close() !!}

        @if ($sentence)
        <p><b>Sentence:</b> <i style='color:#bf5329; font-size: 18px'>{{$sentence}}</i></p>
        @endif
        
        @if ($lemma)
        <p><b>Lemma:</b> <i style='color:#bf5329; font-size: 18px'>{{$lemma}}</i></p>
        @endif
        
        <p>Founded records: {{$numAll}}</p>
        @if ($wordforms)
        <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Wordform</th>
                <th>Lemmas</th>
                <th style='text-align:center'>Number of sentences</th>
                @if (Auth::check())
                <th colspan='2'></th>
                @endif
            </tr>
        </thead>
            @foreach($wordforms as $wordform)
            <?php $wordform_obj = \Wcorpus\Models\Wordform::find($wordform->id); ?>
            <tr>
                <td>{{ $list_count++ }}</td>
                <td>{{$wordform_obj->wordform}}</td>
                <td>
                    @if($wordform->lemmas())
                        @foreach ($wordform->lemmas as $lemma)
                        <a href='/lemma/{{$lemma->id}}'>{{$lemma->lemma}}</a>
                        <span style='font-size:11px; font-style:italic'>({{$lemma->pos->name}})</span><br>
                        @endforeach
                    @endif
                </td>
                <td style='text-align:center'>
                    @if($wordform_obj->sentence_total)
                        <a href="/sentence/?search_wordform={{$wordform_obj->id}}">{{$wordform_obj->sentence_total}}</a><br>
                    @endif
                </td>
                @if (Auth::check())
                <td>
                    @include('widgets.form._button', 
                                 ['is_button'=>true, 
                                  'route' => '/wordform/'.$wordform_obj->id.'/lemmatize',
                                  'title' => 'lemmatize',
                                  'with_args' => false
                                 ])
                </td>
                <td>
                    @include('widgets.form._button_delete', ['is_button'=>true, $route = 'wordform.destroy', 'id' => $wordform_obj->id])                                 
                </td>
                @endif
            </tr>
            @endforeach
        </table>
            {!! $wordforms->appends($url_args)->render() !!}
        @endif

@stop
