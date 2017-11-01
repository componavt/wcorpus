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
        @include('widgets.form._formitem_select',
                ['name' => 'search_author2',
                 'value' =>$url_args['search_author2'],
                 'values' => $author_values,
                 'attributes'=>['placeholder' => 'Author 2' ]])
        @include('widgets.form._formitem_btn_submit', ['title' => 'Compare'])
        view by
        @include('widgets.form._formitem_text',
                ['name' => 'limit_num',
                'value' => $url_args['limit_num'],
                'attributes'=>['size' => 5,
                               'placeholder' => 'Number of records' ]]) records
        {!! Form::close() !!}
        
        @if ($bigrams)
        <table class="table table-striped">
            <tr>
                <th colspan='3'>{{$author_values[$url_args['search_author']]}}</th>
                <th style="text-align:right" rowspan='2'>Lemma 1</th>
                <th rowspan='2'>Lemma 2</th>
                <th colspan='3'>{{$author_values[$url_args['search_author2']]}}</th>
            </tr>
            <tr>
                <th>Frequency (Lemma1 Lemma2)</th>
                <th>Frequency (Lemma1)</th>
                <th><a href="/bigram/?{{$args_by_get}}&order_by=author">Probability</a></th>
                <th>Frequency (Lemma1 Lemma2)</th>
                <th>Frequency (Lemma1)</th>
                <th><a href="/bigram/?{{$args_by_get}}&order_by=author2">Probability</a></th>
            </tr>
            
            @foreach ($bigrams as $bigram)
            <?php
                    if ($url_args['order_by']=='author') {
                        $author = ['count1'=>$bigram->count1, 'count12'=>$bigram->count12, 'probability'=>$bigram->probability];
                        $author2 = \Wcorpus\Models\Bigram::getCountsAndProbability($url_args['search_author2'], $bigram->lemma1, $bigram->lemma2);
                    } else {
                        $author = \Wcorpus\Models\Bigram::getCountsAndProbability($url_args['search_author'], $bigram->lemma1, $bigram->lemma2);
                        $author2 = ['count1'=>$bigram->count1, 'count12'=>$bigram->count12, 'probability'=>$bigram->probability];
                    }
            ?>
            <tr>
                <td>{{$author['count12']}}</td>
                <td>{{$author['count1']}}</td>
                <td>{{$author['probability']}}</td>
                
                <td style="text-align:right">{{\Wcorpus\Models\Lemma::getLemmaWithPOSByID($bigram->lemma1)}} ({{$bigram->lemma1}})</td>
                <td>{{\Wcorpus\Models\Lemma::getLemmaWithPOSByID($bigram->lemma2)}} ({{$bigram->lemma2}})</td>
                
                <td>{{$author2['count12']}}</td>
                <td>{{$author2['count1']}}</td>
                <td>{{$author2['probability']}}</td>
            </tr>
            @endforeach
        </table>
            {!! $bigrams->appends($url_args)->render() !!}
        @endif
@stop

