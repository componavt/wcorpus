<?php 
if(!isset($value) || !$value) 
    $value = null;
?>
    {!! Form::hidden($name, $value) !!}
