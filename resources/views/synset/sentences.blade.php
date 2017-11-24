<?php $list_count = 1;?>
@extends('layouts.page')

@section('title')
Assign lemma synsets to sentences
@stop

@section('panel-heading')
Assign lemma synsets to sentences
@stop

@section('headExtra')
    {!!Html::style('css/sentence.css')!!}
    {!!Html::style('css/tabs.css')!!} 
@stop

@section('panel-body')
        <a href="/synset/sentences">Search another lemma</a>    
            
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
        
        @if ($lemma_id)
        <div>
            <ul class="nav nav-tabs" role="tablist">
            @foreach($synset_sentences as $synset => $info)
                <li{{$synset==NULL ? ' class="active"' : ''}}>
                    <b><a data-toggle="tab" role="tab" href="#panel{{$synset==NULL ? '' : $info[0]}}">
                            {{$synset==NULL ? 'Without synsets' : $info[0].' synset'}}
                        </a></b> ({{sizeof($info[1])}})
                </li>
            @endforeach
            </ul>
        
            <div class="tab-content tabs">
            @foreach($synset_sentences as $synset => $info)
                <div id="panel{{$synset==NULL ? '' : $info[0]}}" role="tabpanel" class="tab-pane fade{{$synset==NULL ? ' in active' : ''}}">
                @foreach($info[1] as $sentence)
                    <div class="row">
                        <div class="col col-sm-1" style='text-align:right'>{{ $list_count++ }}</div>
                        <div class="col col-sm-8">{!!$sentence->highlightLemmas([$lemma_id])!!}</div>
                        <div class="col col-sm-3">
                        @include('widgets.form._formitem_select',
                                ['name' => 'sentence_synset['.$sentence->id.']',
                                 'value' => $synset,
                                 'values' => $synset_values,
                                 'attributes'=>['placeholder' => 'Choose synset' ]])
                        </div>            
                    </div>
                @endforeach
                </div>
            @endforeach
            </div>
        </div>            
            @include('widgets.form._formitem_btn_submit', ['title' => 'Save'])
        @endif
        {!! Form::close() !!}
@stop

@section('footScriptExtra')
    {!!Html::script('js/bootstrap-tab.js')!!}
@stop

