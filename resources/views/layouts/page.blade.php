<?php
        list($usec, $sec) = explode(" ", microtime());
        $start_time = (float)$usec + (float)$sec; // set start time of execution
?>
@extends('layouts.master')

@section('content')
<div class="container">
    <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h1>@yield('panel-heading')</h1>
                </div>

                <div class="panel-body">
@yield('panel-body')
<?php
        list($usec, $sec) = explode(" ", microtime());
        $execution_time = (float)$usec + (float)$sec - $start_time;
?>
                <p style="text-align:right; font-style: italic">{{sprintf('Page generated in %f seconds.',$execution_time)}}</p>
            </div>
        </div>
    </div>
</div>
@endsection
