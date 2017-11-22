<?php $list_count = 1;?>
@extends('layouts.page')

@section('title')
Assign sentences to lemma synsets
@stop

@section('panel-heading')
Assign sentences to lemma synsets
@stop

@section('headExtra')
    {!!Html::style('css/sentence.css')!!}
@stop

@section('panel-body')
        {!! Form::open(['url' => '/synset/sentences/',
                             'method' => 'get',
                             'class' => 'form-inline'])
        !!}
        @include('widgets.form._formitem_select',
                ['name' => 'lemma_id',
                 'value' => $lemma_id,
                 'values' => $lemma_values,
                 'attributes'=>['placeholder' => 'Lemma' ]])
        @include('widgets.form._formitem_btn_submit', ['title' => $lemma_id ? 'Save' : 'Search'])
        
        @if ($sentences)
        <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Sentence</th>
                <!--th>Text</th-->
                <th>Synset</th>
            </tr>
        </thead>
            @foreach($sentences as $sentence)
            <tr>
                <td>{{ $list_count++ }}</td>
                <td>
                    {!!$sentence->highlightLemmas([$lemma_id])!!}
                </td>
{{--                <td>
                    @if($sentence->text)
                        <a href="text/{{$sentence->text_id}}">{{$sentence->text->title}}</a>
                    @endif--}}
                <!--/td--> 
                <td>
                @include('widgets.form._formitem_select',
                        ['name' => 'sentence_synset[$sentence->id]',
                         'value' => $sentence_synset[$sentence->id],
                         'values' => $synset_values,
                         'attributes'=>['placeholder' => 'Choose synset' ]])
                </td>            
            </tr>
            @endforeach
        </table>
        @endif
        @include('widgets.form._formitem_btn_submit', ['title' => $lemma_id ? 'Save' : 'Search'])
        {!! Form::close() !!}
@stop
