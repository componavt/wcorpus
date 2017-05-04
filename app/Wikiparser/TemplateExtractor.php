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
     * 
     * @return String
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
        $failed = false;
        $parameter_count = 1;
        
        while (!$found && !$failed) {
            $end_pos = strpos($wikitext, '|',$start_pos);
            if ($end_pos===false) { // нет больше |, остались только }}
                $end_pos = strpos($wikitext, '}}',$start_pos);
            }
//print "\n\n$parameter_count. start: $start_pos, end: $end_pos\n";            
            if ($end_pos===false) { // нет ни |, ни }}, возьмем все до конца строки
                $result = substr($wikitext, $start_pos);
                if ($parameter_number == $parameter_count) {
                    $found = true;
                } else {
                    $failed = true;
                }
                continue;
            } 
            $result = substr($wikitext, $start_pos, $end_pos-$start_pos);
            
            
            // Looks for internal templates and deletes them
            $template_inside_start_pos = strpos($wikitext, "{{",$start_pos);
            while ($template_inside_start_pos!==false && $template_inside_start_pos < $end_pos) {
                $template_inside_end_pos = strpos($wikitext, "}}",$template_inside_start_pos);
                
                $end_pos = strpos($wikitext, '|',$template_inside_end_pos);
                if ($end_pos===false) { // нет больше |, остались только }}
                    $end_pos = strpos($wikitext, '}}',$template_inside_end_pos+2);
                }
                if ($end_pos===false) { // нет ни |, ни }}, возьмем все до конца строки
                    $result = substr($wikitext, $start_pos);
                    if ($parameter_number == $parameter_count) {
                        $found = true;
                    } else {
                        $failed = true;
                    }
                    continue;
                } else {
                    $result = substr($wikitext, $start_pos, $end_pos-$start_pos);
                }
                     
                $template_inside_start_pos = strpos($wikitext, "{{",$template_inside_end_pos);
            }

//print "\nstart: $start_pos, end: $end_pos, result: $result\n";            
            if ($parameter_number == $parameter_count) {
                $found = true;
            } else {
                $start_pos = $end_pos+1;
                $parameter_count ++;
            }
        }
        
        $result = trim($result);
        if ($found) {
            return $result;
        } else {
            return '';
        }
    }
    
    
    /** Remove all occurences of the template from wiki text
     * 
     * @param String $template_name
     * @param String $wikitext
     * 
     * @return String
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

        while ($start_pos!==false && $end_pos!==false) {
//print "\n\n$result\n$start_pos=$end_pos\n";            
            $result = substr($result, 0, $start_pos)
                    . substr($result, $end_pos + 2);
            $start_pos = strpos($result, $template);
            $end_pos = strpos($result, '}}', $start_pos);
        }

//print "\nstart: $start_pos, end: $end_pos, result: $result\n";            
        
        return $result;
    }
    
    
    /** Replace wiki link to plain text
     * 
     * @param String $wikitext
     * 
     * @return String
     */
    public static function removeWikiLinks(String $wikitext) : String
    {
        if (!$wikitext) {
            return '';
        }
        while (preg_match("/^(.*)\[\[([^\]]+)\]\](.*)/us",$wikitext,$regs)) {
            if (preg_match("/^[^\|]+\|(.+)$/",$regs[2],$regs1)) {
                $regs[2] = $regs1[1];
            }
            $wikitext = $regs[1].$regs[2].$regs[3];
        }
        
        return $wikitext;
    }    
    
    /** Remove html-comments <!-- -->
     * 
     * If the end --> is missed all text before <!-- is removed
     * 
     * @param String $wikitext
     * 
     * @return String
     */
    public static function removeComments(String $wikitext) : String
    {
        if (!$wikitext) {
            return '';
        }
            
        $start_pos = mb_strpos($wikitext, "<!--");
        $end_pos = true;

        while ($start_pos!==false && $end_pos!==false) {
            $end_pos = mb_strpos($wikitext, "-->", $start_pos+4);
            $newtext = mb_substr($wikitext,0,$start_pos);
            if ($end_pos!==false) {
                $newtext .= mb_substr($wikitext,$end_pos+3);
            }
            $wikitext = $newtext;
            $start_pos = mb_strpos($wikitext, "<!--");
        }
        return $wikitext;
    }    
    
    /** Replace temlates lang to plain text
     * 
     * {{lang|he|והיה כי יארכו הימים}}
     * {{lang-en|сезон}}
     * {{lang-it|}}
     * 
     * @param String $wikitext
     * 
     * @return String
     */
    public static function removeLangTemplates(String $wikitext) : String
    {
        if (!$wikitext) {
            return '';
        }
        
        $wikitext = preg_replace("/\{\{lang\s*\|\s*[^\|]+\|([^\}]*)\}\}/iu","\\1",$wikitext);
        $wikitext = preg_replace("/\{\{lang\-[^\|]+\|([^\}]*)\}\}/iu","\\1",$wikitext);

        return $wikitext;
    }    
    
    /** Replace wiki link to plain text
     * 
     * @param Array $text_info = ['text'=><wikitext>,
                                  'title' => null,
                                  'creation_date' => null
     * 
     * @return Array
     */
    public static function extractPoetry(Array $text_info) : Array
    {
        if (!$text_info['text']) {
            return $text_info;
        }
        
        if (preg_match("/\{\{Poemx?\|([^\|]*)\|(\<poem\>)*([^\|]+)(\<\/poem\>)*\|([^\}]*)\}\}/i",$text_info['text'],$regs)) {
            $text_info['title'] = trim($regs[1]);
            $text_info['text'] = trim($regs[3]);
            $text_info['creation_date'] = trim($regs[5]);
            if (mb_strlen($text_info['creation_date'])>50) {
                $text_info['creation_date'] = mb_substr($creation_date,0,50);
            }
        } 
        
        return $text_info;
    }    
    
    /** Remove all occurences of templates from wiki text
     * 
     * @param String $wikitext
     * 
     * @return String
     */
    public static function removeAnyTemplates(String $wikitext) : String
    {
        if( !$wikitext ) {
            return '';
        }
        
//        $wikitext = preg_replace("/(\{\{[^\}]*\}\})/","",$wikitext);

        while (preg_match("/^(.*)\{\{[^\}]*\}\}(.*)$/us",$wikitext,$regs)) {
            $wikitext = trim($regs[1].$regs[2]);
        }
        return $wikitext;
    }
    
}
