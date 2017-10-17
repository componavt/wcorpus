@extends('layouts.page')

@section('title')
Statistics
@stop

@section('panel-heading')
Statistics
@stop

@section('panel-body')
    <p>Number of texts: <big>{{$stats['text_included']}} from {{$stats['text_total']}}</big></p>
    <p>Number of sentences: <big>{{$stats['sentence_total']}}</big>. 
    <br>The sentences with less than 3 words were deleted</p>
    <p>Number of wordforms (W): <big>{{$stats['wordform_total']}}</big>
    <br>Number of wordforms without lemmas (Wo): <big>{{$stats['wordform0_total']}}</big>
    <br>Number of wordforms with predicted lemmas (Wp): <big>{{$stats['predicted_wordform_total']}}</big>
    <br>W - Wo - Wp = {{$stats['wordform_clear_total']}} 
    </p>
    <p>Number of lemmas (L): <big>{{$stats['lemma_total']}}</big>
    <br>Number of predicted lemmas (Lp): <big>{{$stats['lemma_predicted_total']}}</big>
    <br>L - Lp = <big>{{$stats['lemma_clear_total']}}</big>
    </p>

    <h3>Tables:</h3>
    @foreach($tables as $tname => $count)
        {{$tname}}: {{$count}}<br>
    @endforeach
@stop

