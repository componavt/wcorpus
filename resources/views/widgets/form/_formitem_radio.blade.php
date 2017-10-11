<?php
if(! isset($values)) {
    $values = [];
} elseif(!is_array($values)) {
    $values = (array)$values;
}   
if(! isset($value)) $value = null;
if(! isset($title)) $title = null;
?>
<div class="{!! $errors->has($name) ? 'has-error' : null !!}">
    {{ $title }}
    @foreach($values as $v=>$t)
	<label>{{ $t }}
    {!! Form::radio($name, $v, $v===$value) !!}
        </label>
    @endforeach 
    <p class="help-block">{!! $errors->first($name) !!}</p>
</div>