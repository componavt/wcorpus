<?php
        if (isset($url_args) && sizeof($url_args)) {
            foreach ($url_args as $a=>$v) {
                if (is_array($v)) {
                    foreach ($v as $v_elem) { ?>
<input type="hidden" name="{{$a}}[]" value="{{$v_elem}}">
                        
<?php                    }
                } elseif ($v!='') {?>
<input type="hidden" name="{{$a}}" value="{{$v}}">
<?php           }
            }
        }
?>
