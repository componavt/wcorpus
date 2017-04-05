<?php
/* TemplateExtractor.php - set of functions to extract {{template data|from the text}}.
 * 
 * Copyright (c) 2013 Andrew Krizhanovsky <andrew.krizhanovsky at gmail.com>
 * Distributed under EPL/LGPL/GPL/AL/BSD multi-license.
 */
namespace Wcorpus\Wikiparser;

use Illuminate\Database\Eloquent\Model;

class TemplateExtractor
{
    /** extracts a text of second parameter from the template {{Poemx|1|2|3}}
     * 
     * @param String $template_name
     * @param int $parameter_number
     * @param String $wikitext which contains template
     */
    public static function getParameterValueWithoutNames(String $template_name,int $parameter_number,String $wikitext) : String
    {
        $result = '';
        if( !$wikitext ) {
            return "";
        }
        
        $template = "{{".$template_name."|";
        
        $pos = strpos($wikitext, $template);
        if ($pos===false) {
            return '';
        }
        
        $start_pos = $pos + strlen($template);
        $found = false;
        $parameter_count = 1;
        
        while (!$found) {
            $end_pos = strpos($wikitext, '|',$start_pos);

            $result = substr($wikitext, $start_pos, $end_pos-$start_pos);

//print "\nstart: $start_pos, end: $end_pos, result: $result\n";            
            if ($parameter_number == $parameter_count) {
                $found = true;
            } else {
                $start_pos = $end_pos+1;
                $parameter_count ++;
            }
        }
        return $result;
    }
    
    
    /** Remove all occurences of the template from wiki text
     * 
     * @param String $template_name
     * @param String $wikitext
     */
    public static function removeTemplate(String $template_name, String $wikitext) : String
    {
        $result = $wikitext;
        if( !$wikitext ) {
            return $result;
        }
        
        $template = "{{".$template_name."|";
        
        $start_pos = strpos($wikitext, $template);
        $end_pos = strpos($wikitext, '}}', $start_pos);
        if ($start_pos === false || $end_pos === false) {
            return $result;
        }
        
        
        if ($end_pos === false) {
            return $result;
        }
        
        $result = substr($wikitext, 0, $start_pos)
                . substr($wikitext, $end_pos + 2);

//print "\nstart: $start_pos, end: $end_pos, result: $result\n";            
        
        return $result;
    }    
}
