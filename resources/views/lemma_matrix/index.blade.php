<?php $list_count = $url_args['limit_num'] * ($url_args['page']-1) + 1;?>
@extends('layouts.page')

@section('title')
Lemma matrix
@stop

@section('panel-heading')
Lemma matrix
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
        {!! Form::close() !!}

        <p>Founded records: {{$numAll}}</p>
        @if ($matrix)
        <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Lemma1</th>
                <th>Lemma2</th>
                <th><a href="lemma_matrix/{{$args_by_get}}&order_by=freq_12">Frequency 1-2</a></th>
                <th><a href="lemma_matrix/{{$args_by_get}}&order_by=freq_21">Frequency 2-1</a></th>
            </tr>
        </thead>
            @foreach($matrix as $pair)
            <?php 
//dd($pair);            
                $lemma1 = \Wcorpus\Models\Lemma::find($pair->lemma1); 
                $lemma2 = \Wcorpus\Models\Lemma::find($pair->lemma2); 
            ?>
            <tr>
                <td>{{ $list_count++ }}</td>
                <td>{{$lemma1->lemma}}</td>
                <td>{{$lemma2->lemma}}</td>
                <td>{{$pair->freq_12}}</td>
                <td>{{$pair->freq_21}}</td>
            </tr>
            @endforeach
        </table>
            {!! $matrix->appends($url_args)->render() !!}
        @endif

@stop
