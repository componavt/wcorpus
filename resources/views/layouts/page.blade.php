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
            </div>
        </div>
    </div>
</div>
@endsection
