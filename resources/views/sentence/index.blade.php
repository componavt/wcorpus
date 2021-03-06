<?php $list_count = $url_args['limit_num'] * ($url_args['page']-1) + 1;?>
@extends('layouts.page')

@section('title')
List of sentences
@stop

@section('panel-heading')
List of sentences
@stop

@section('headExtra')
    {!!Html::style('css/select2.min.css')!!}
    {!!Html::style('css/sentence.css')!!}
@stop

@section('panel-body')
        {!! Form::open(['url' => '/sentence/',
                             'method' => 'get',
                             'class' => 'form-inline'])
        !!}
        @include('widgets.form._formitem_text',
                ['name' => 'search_lemma',
                'value' => $url_args['search_lemma'],
                'attributes'=>['placeholder' => 'Lemma' ]])
                               
        @include('widgets.form._formitem_select2',
                ['name' => 'search_text',
                 'value' =>$url_args['search_text'],
                 'values' => $text_values,
                 'is_multiple' => false,
                 'class'=>'multiple-select-text form-control',
                 'attributes'=>['placeholder' => 'Text' ]])
        
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
        @include('widgets.form._formitem_hidden',
                ['name' => 'search_author',
                'value' => $url_args['search_author']]) 
        @include('widgets.form._formitem_hidden',
                ['name' => 'bigram_lemma1',
                'value' => $url_args['bigram_lemma1']]) 
        @include('widgets.form._formitem_hidden',
                ['name' => 'bigram_lemma2',
                'value' => $url_args['bigram_lemma2']]) 
        {!! Form::close() !!}

        @if ($wordform)
        <p><b>Wordform:</b> <i style='color:#bf5329; font-size: 18px'>{{$wordform}}</i></p>
        @endif
        
        <p>Founded records: {{$numAll}}</p>
        @if ($sentences)
        <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Sentence</th>
                <th>Text</th>
                <th>Wordforms</th>
                @if (Auth::check())
                <th colspan='2'></th>
                @endif
            </tr>
        </thead>
            @foreach($sentences as $sentence)
            <?php $sentence_obj = \Wcorpus\Models\Sentence::find($sentence->id); ?>
            <tr>
                <td>{{ $list_count++ }}</td>
                <td>
                @if($bigram_lemma1 || $bigram_lemma2)
                    {!!$sentence_obj->highlightLemmas([$bigram_lemma1,$bigram_lemma2])!!}
                @elseif ($url_args['search_lemma'])
                    {!!$sentence_obj->highlightLemmas($lemmas)!!}
                @elseif ($wordform)
                    {!!$sentence_obj->highlightWordform($wordform)!!}
                @else
                    {{$sentence_obj->sentence}}
                @endif
                </td>
                <td>
                    @if($sentence_obj->text)
                        <a href="text/{{$sentence_obj->text_id}}">{{$sentence_obj->text->title}}</a>
                    @endif
                </td>
                <td style='text-align:center'>
                    @if($sentence->wordforms())
                        <a href="/wordform/?search_sentence={{$sentence_obj->id}}">{{$sentence->wordforms()->count()}}</a><br>
                    @endif
                </td>
                @if (Auth::check())
                <td>
                    @include('widgets.form._button', 
                                 ['is_button'=>true, 
                                  'route' => '/sentence/'.$sentence_obj->id.'/break_into_words',
                                  'title' => 'split',
                                  'with_args' => false
                                 ])
                </td>
                <td>
                    @include('widgets.form._button_delete', ['is_button'=>true, $route = 'sentence.destroy', 'id' => $sentence_obj->id])                                 
                </td>
                @endif
            </tr>
            @endforeach
        </table>
            {!! $sentences->appends($url_args)->render() !!}
        @endif

@stop

@section('footScriptExtra')
    {!!Html::script('js/select2.min.js')!!}
@stop

@section('jqueryFunc')
    $(".multiple-select-text").select2({
        width: '300px',
        ajax: {
          url: "/text/title_list",
          dataType: 'json',
          delay: 250,
          data: function (params) {
            return {
              q: params.term // search term
            };
          },
          processResults: function (data) {
            return {
              results: data
            };
          },          
          cache: true
        }
    });
    
@stop
