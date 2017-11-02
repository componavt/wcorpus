<?php
/**
 * Created by PhpStorm.
 * User: Dmitriy Pivovarov aka AngryDeer http://studioweb.pro
 * Date: 25.01.16
 * Time: 4:46
 */
$attr['class'] = 'btn btn-primary btn-default';
if (isset($style)) {
$attr['style'] = $style;
}
?>
<div class="form-group">
{!! Form::submit($title, $attr) !!}
</div>