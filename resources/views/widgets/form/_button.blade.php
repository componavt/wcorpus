<?php
        if (!isset($with_args) || $with_args) {
            if (isset($args_by_get)) {
                $route .= $args_by_get;
            } elseif (isset($url_args) && sizeof($url_args)) {
                $tmp=[];
                foreach ($url_args as $a=>$v) {
                    if ($v!='') {
                        $tmp[] = "$a=$v";
                    }
                }
                if (sizeof ($tmp)) {
                    $route .= "?".implode('&',$tmp);
                }
            }
        
        }
        
        $format = '<a  href="%s"';
        if (isset($is_button) && $is_button) {
            $format .= ' class="btn btn-warning btn-xs btn-detail"';
        }
        $format .= '>%s</a>';
        if (isset($without_text) && $without_text) {
            $title = '';
        }
        
        print sprintf($format, $route, $title);