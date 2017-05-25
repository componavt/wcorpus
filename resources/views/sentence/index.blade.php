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
@stop

@section('panel-body')
        {!! Form::open(['url' => '/text/',
                             'method' => 'get',
                             'class' => 'form-inline'])
        !!}
        @include('widgets.form._formitem_select2',
                ['name' => 'search_text',
                 'value' =>$url_args['search_text'],
                 'class'=>'multiple-select-text form-control',
                 'attributes'=>['placeholder' => 'Text' ]])
        
        @include('widgets.form._formitem_btn_submit', ['title' => 'View'])

        by
        @include('widgets.form._formitem_text',
                ['name' => 'limit_num',
                'value' => $url_args['limit_num'],
                'attributes'=>['size' => 5,
                               'placeholder' => 'Number of records' ]]) records
        {!! Form::close() !!}

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
                <td>{{$sentence_obj->sentence}}</td>
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
