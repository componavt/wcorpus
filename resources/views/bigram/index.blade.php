<?php $count=1;?>
@extends('layouts.page')

@section('title')
Comparision of bigrams for authors
@stop

@section('panel-heading')
Comparision of bigrams for authors
@stop

@section('panel-body')
        {!! Form::open(['url' => '/bigram/',
                             'method' => 'get',
                             'class' => 'form-inline'])
        !!}
        @include('widgets.form._formitem_select',
                ['name' => 'search_author',
                 'value' =>$url_args['search_author'],
                 'values' => $author_values,
                 'attributes'=>['placeholder' => 'Author 1' ]])
                 <a href="/bigram/{{\Wcorpus\Models\Bigram::authorConversely($url_args)}}"><i class="fa fa-exchange fa-2x" style="padding-left:20px; padding-right: 20px" title="Replace"></i></a>
        @include('widgets.form._formitem_select',
                ['name' => 'search_author2',
                 'value' =>$url_args['search_author2'],
                 'values' => $author_values,
                 'attributes'=>['placeholder' => 'Author 2' ]])
        @include('widgets.form._formitem_btn_submit', ['title' => 'Compare',
                "style"=>"margin-left:20px; margin-right: 20px",
        ])
        view
        @include('widgets.form._formitem_text',
                ['name' => 'limit_num',
                'value' => $url_args['limit_num'],
                'attributes'=>['size' => 5,
                               'placeholder' => 'Number of records' ]]) records

        <br>  
        Minimum of frequency (Lemma1 Lemma2) 
        @include('widgets.form._formitem_text',
                ['name' => 'min_count12',
                'value' => $url_args['min_count12'],
                'attributes'=>['size' => 5,
                               'style'=>'margin-left:20px; margin-right: 20px' ]]) 

        Min of freq (L1) 
        @include('widgets.form._formitem_text',
                ['name' => 'min_count1',
                'value' => $url_args['min_count1'],
                'attributes'=>['size' => 5,
                               'style'=>'margin-left:20px' ]]) 

        Max of freq (Lemma1 Lemma2) 
        @include('widgets.form._formitem_text',
                ['name' => 'max_count12',
                'value' => $url_args['max_count12'],
                'attributes'=>['size' => 5,
                               'style'=>'margin-left:20px; margin-right: 20px' ]]) 

        Max of freq (L1) 
        @include('widgets.form._formitem_text',
                ['name' => 'max_count1',
                'value' => $url_args['max_count1'],
                'attributes'=>['size' => 5,
                               'style'=>'margin-left:20px' ]]) 

        {!! Form::close() !!}

        @if ($bigrams)
        <table class="table table-striped">
            <tr>
                <th rowspan='2'>No</th>
                <th colspan='3'style="text-align:middle">{{$author_values[$url_args['search_author']]}}</th>
                <th style="text-align:right" rowspan='2'>Lemma 1</th>
                <th rowspan='2'>Lemma 2</th>
                <th colspan='3'>{{$author_values[$url_args['search_author2']]}}</th>
            </tr>
            <tr>
                <th><a href="/bigram/?{{$args_by_get}}&order_by=count12">Frequency (Lemma1 Lemma2)</a></th>
                <th><a href="/bigram/?{{$args_by_get}}&order_by=count1">Frequency (Lemma1)</a></th>
                <th><a href="/bigram/?{{$args_by_get}}&order_by=probability">Probability</a></th>
                <th>Frequency (Lemma1 Lemma2)</th>
                <th>Frequency (Lemma1)</th>
                <th>Probability</th>
            </tr>
            
            @foreach ($bigrams as $bigram)
            <?php   if ($count > $url_args['limit_num'])
                        break;
                    $author2 = \Wcorpus\Models\Bigram::getCountsAndProbability(
                                            $url_args['search_author2'], 
                                            $bigram->lemma1, 
                                            $bigram->lemma2, 
                                            $url_args['max_count1'], 
                                            $url_args['max_count12']); 
            ?>
            
                @if ($author2!==false) 
            <tr>
                <td>{{$count}}</td>
                <td>{{$bigram->count12}}</td>
                <td>{{$bigram->count1}}</td>
                <td>{{$bigram->probability}}</td>
                
                <td style="text-align:right">{{$bigram->lemma1 ? \Wcorpus\Models\Lemma::getLemmaWithPOSByID($bigram->lemma1) : "<s>"}} {{--({{$bigram->lemma1}})--}}</td>
                <td>{{$bigram->lemma2 ? \Wcorpus\Models\Lemma::getLemmaWithPOSByID($bigram->lemma2) : "</s>"}} {{--({{$bigram->lemma2}})--}}</td>
                
                <td>{{$author2['count12']}}</td>
                <td>{{$author2['count1']}}</td>
                <td>{{$author2['probability']}}</td>
            </tr>
                <?php $count++;?>
                @endif
            @endforeach
        </table>
        {{--    {!! $bigrams->appends($url_args)->render() !!}  --}}
        @endif
@stop

