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
                'attributes'=>['placeholder' => 'Lemma' ]]) 
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
                <th><a href="lemma/{{$args_by_get}}&order_by=lemma">Lemma</a></th>
                <th>POS</th>
                <th>Wordforms</th>
                <th><a href="lemma/{{$args_by_get}}&order_by=freq">Frequency</a></th>
            </tr>
        </thead>
            @foreach($lemmas as $lemma)
            <?php $lemma_obj = \Wcorpus\Models\Lemma::find($lemma->id); ?>
            <tr>
                <td>{{ $list_count++ }}</td>
                <td>{{$lemma_obj->lemma}}</td>
                <td>{{$lemma_obj->pos->name}}</td>
                <td>
                    @if($lemma->wordforms())
                        @foreach ($lemma->wordforms as $wordform)
                            {{$wordform->wordform}}
                        @endforeach
                    @endif
                </td>
                <td>
                    @if($lemma->freq)
                        {{$lemma->freq}}<br>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
            {!! $lemmas->appends($url_args)->render() !!}
        @endif

@stop
