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
        
        $pos = mb_strpos($wikitext, $template);
        if ($pos===false) {
            return '';
        }
        
        $start_pos = $pos + mb_strlen($template);
        $found = false;
        $failed = false;
        $parameter_count = 1;
        $is_end = false; // end is reached
        
        while (!$found && !$failed) {
            $end_pos = mb_strpos($wikitext, '|',$start_pos);
            if ($end_pos===false) { // нет больше |, остались только }}
                $end_pos = mb_strpos($wikitext, '}}',$start_pos);
            }
            if ($end_pos===false) { // нет ни |, ни }}, возьмем все до конца строки
                $result = mb_substr($wikitext, $start_pos);
                if ($parameter_number == $parameter_count) {
                    $found = true;
                } else {
                    $failed = true;
                }
                continue;
            } 
            $result = mb_substr($wikitext, $start_pos, $end_pos-$start_pos);
            
            // Looks for internal templates and deletes them
            $template_inside_start_pos = mb_strpos($wikitext, "{{",$start_pos);
            while ($template_inside_start_pos!==false && $template_inside_start_pos < $end_pos) {
                $template_inside_end_pos = mb_strpos($wikitext, "}}",$template_inside_start_pos);
                
                $end_pos = mb_strpos($wikitext, '|',$template_inside_end_pos+2);
                if ($end_pos===false) { // нет больше |, остались только }}
                    $end_pos = mb_strpos($wikitext, '}}',$template_inside_end_pos+2);
                    $is_end = true;
                }
                if ($end_pos===false) { // нет ни |, ни }}, возьмем все до конца строки
                    $is_end = true;
                    $result = mb_substr($wikitext, $start_pos);
                    if ($parameter_number == $parameter_count) {
                        $found = true;
                    } else {
                        $failed = true;
                    }
                    continue;
                } else {
                    $result =mb_substr($wikitext, $start_pos, $end_pos-$start_pos);
                }
                     
                $template_inside_start_pos = mb_strpos($wikitext, "{{",$template_inside_end_pos);
            }

//print "\nstart: $start_pos, end: $end_pos, result: $result\n";            
            if ($parameter_number == $parameter_count) {
                $found = true;
                
            // The end is reached earlier than the parameter $parameter_number is found
            } elseif ($is_end && $parameter_number > $parameter_count) { 
                $found = true;
                 $result = '';
            } else {
                $start_pos = $end_pos+1; // сдвиг на |
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
    
    /** Clear text from references <ref...>..</ref>
     * 
     * @param String $wikitext
     * 
     * @return String
     */
    public static function removeRefTags(String $wikitext) : String
    {
        if( !$wikitext ) {
            return '';
        }
        $wikitext = preg_replace("/\<ref.+\<\/ref\>/sU","",$wikitext);

        return trim($wikitext);
    }
    
    /** Replace wiki link to plain text
     * 
     * @param Array $text_info = ['text'=><wikitext>,
                                  'title' => null,
                                  'creation_date' => null
        не проходит тест с эпиграфом!!!   
     * 
     * @return Array
     */
    public static function extractPoetry(Array $text_info) : Array
    {
        if (!$text_info['text']) {
            return $text_info;
        }
        
        while (preg_match("/^(.*\{\{poemx?\|[^\{]*)\{\{[^\}]*\}\}([^\}]*\}\}.*)$/i",$text_info['text'],$regs)) {
            $text_info['text'] = $regs[1].$regs[2];
        }
        
        if (preg_match("/\{\{poemx?\|([^\|]*)\|(\<poem\>)*([^\|]+)(\<\/poem\>)*\|([^\}]*)\}\}/i",$text_info['text'],$regs)) {
            $text_info['title'] = trim($regs[1]);
/*            $new_title = trim($regs[1]);
            if (!$text_info['title'] && $new_title || $text_info['title'] && $new_title!="?" && $new_title!="* * *") {
                $text_info['title'] = $new_title;
            }*/
            
            $text_info['text'] = trim($regs[3]);
            $text_info['creation_date'] = trim($regs[5]);
        } 
        
        return $text_info;
    }    
    
    /** Replace wiki link to plain text
     * 
     * @param Array $text_info = ['text'=><wikitext>,
                                  'title' => null,
                                  'creation_date' => null
        не проходит тест с эпиграфом!!!   
     * 
     * @return Array
     */
    public static function extractPoem_on(Array $text_info) : Array
    {
        if (!$text_info['text']) {
            return $text_info;
        }
        
        if (preg_match("/\{\{poem-on\|([^\}]*)\}\}(.+)\{\{poem-off\|([^\}]*)\}\}/is",$text_info['text'],$regs)) {
//        if (preg_match("/\{\{poem-on\|([^\}]*)\}\}(.+)/is",$text_info['text'],$regs)) {
//print_r($regs);            

            $text_info['title'] = trim($regs[1]);
/*          $new_title = trim($regs[1]);
            if (!$text_info['title'] && $new_title || $text_info['title'] && $new_title!="?" && $new_title!="* * *") {
                $text_info['title'] = $new_title;
            } */
            
            $text_info['text'] = $regs[2];
            $text_info['creation_date'] = trim($regs[3]);
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
    
    /** Remove "tail" of text, beginning from phrase "== Примечания ==" 
     * 
     * @param String $wikitext
     * 
     * @return String
     */
    public static function removeTale(String $wikitext) : String
    {
        if( !$wikitext ) {
            return '';
        }
        $search_str = '== Примечани[яе] ==';
        
        if (preg_match("/^(.*)".$search_str."/siu",$wikitext,$regs)) {
            $wikitext = trim($regs[1]);
        }
        
/*        
        $search_str = '== Примечание ==';
        
        $pos = mb_strpos($wikitext, $search_str);
        
        if ($pos !== false) {
            $wikitext = trim(mb_substr($wikitext, 0, $pos));
        }
*/        
        return $wikitext;
    }
    
    /** Clear text from  templates, tags, title such as == ... ==, 
     * magic words, such as __NOTC__, categories "Категория: .."; referances
     * 
     * @param String $wikitext
     * 
     * @return String
     */
    public static function clearText(String $wikitext) : String
    {
        if( !$wikitext ) {
            return '';
        }
        
        //{{Noindent|no ni odin ne ostavil vo mne stol' dolgogo, stol' priyatnogo vospominaniya.}}
        $tags_for_format = ['Noindent']; // безобидные теги для форматирования, удаляем теги, внутренний текст оставляем
        foreach ($tags_for_format as $template_name) {
        while (preg_match("/\{\{".$template_name."\|/",$wikitext)) {
            $splited_text = TemplateExtractor::divideByTemplate($wikitext,$template_name);
            if (preg_match("/^\{\{".$template_name."\|(.*)\}\}$/",$splited_text[1],$regs)) {
                $splited_text[1] = $regs[1];
            }
            $wikitext  = join('',$splited_text);
        }
        }

        
        // remove another templates inside text
        while (preg_match("/^(.*)\{\{[^\}]+\}\}(.*)$/s",$wikitext,$regs)) {
            $wikitext = $regs[1].$regs[2];
        }
        
        //remove references
        $wikitext = preg_replace("/\<ref.+\<\/ref\>/sU","",$wikitext);
//        $wikitext = preg_replace("/\<ref[^\<]+\<\/ref\>/","",$wikitext);

        // titles
        $wikitext = preg_replace("/(^={1,}.+$)/m","",$wikitext);

        // categories
        $wikitext = preg_replace("/(^Категория:.*$)/mu","",$wikitext);

        // magic words
        $wikitext = preg_replace("/(__[^_]+__)/","",$wikitext);

        //tags
        $wikitext = strip_tags($wikitext);
        return trim($wikitext);
    }
    
    /** Clear date from  templates, tags, referances
     * 
     * @param String $wikitext
     * 
     * @return String
     */
    public static function clearDate(String $wikitext) : String
    {
        if( !$wikitext ) {
            return '';
        }
        // remove another templates inside text
//print "\n$wikitext\n";        
        while (preg_match("/^(.*)\{\{[^\}]+(\}\})?(.*)$/s",$wikitext,$regs)) {
            $wikitext = $regs[1].$regs[3];
//print "\n$wikitext\n";        
        }
        
        //remove referances
        $wikitext = preg_replace("/\<ref[^\<]+\<\/ref\>/","",$wikitext);

        //tags
        //$wikitext = strip_tags($wikitext);
        return trim($wikitext);
    }
    
    /** Extract title of publication 
     * in template "{{Отексте"
     * OR 
     * in another template
     * by searching string "| НАЗВАНИЕ"
     * 
     * @param String $wikitext
     * 
     * @return String
     */
    public static function extractTitle(String $wikitext) : String
    {
        if( !$wikitext ) {
            return '';
        }
//print ($wikitext);    
        $title = '';
        
        if (preg_match("/\{\{О\s?тексте[^\}]+НАЗВАНИЕ\s*=\s*\[*([^\|\]\}]+)/",$wikitext,$regs)) {
            $title = trim($regs[1]);
            
            if (preg_match("/^([^\[]*)\[\[([^\|\]]+\|?[^\]]*\]\](.*)$)/",$title,$regs1)) {
                $title = $regs1[1].$regs1[2].$regs1[3];                
            }
            
        } elseif (preg_match("/\|\s*НАЗВАНИЕ\s*=\s*\[*([^\|\]\}]+)/",$wikitext,$regs)) {
            $title = trim($regs[1]);
            
            if (preg_match("/^\([^\[]*)\[\[([^\|\]]+\|?[^\]]*\]\](.*)$)/",$title,$regs1)) {
                $title = $regs1[1].$regs1[2].$regs1[3];                
            }
            
        }
        
        if (preg_match("/^\<(.+)\>$/",$title,$regs)) {
            $title = $regs[1];                
        }
        
        if ((!$title || $title=='?') && preg_match("/\{\{О\s?тексте[^\}]+ПОДЗАГОЛОВОК\s*=\s*\[*([^\|\]\}]+)/",$wikitext,$regs)) {
            $title = trim($regs[1]);
        }
//print "\n______\n$title\n___________\n";        
        $title = TemplateExtractor::clearText($title);

        return $title;
    }
    
    /** Extract date of publication 
     * in template "{{Отексте"
     * OR 
     * in another template
     * by searching string "| ДАТАСОЗДАНИЯ=" or "| ДАТАПУБЛИКАЦИИ="
     * 
     * @param String $wikitext
     * 
     * @return String
     */
    public static function extractDate(String $wikitext) : String
    {
        if( !$wikitext ) {
            return '';
        }
        
        $date = '';
        
        if (preg_match("/\{\{О\s?тексте[^\}]+ДАТАСОЗДАНИЯ\s*=(.+)$/m",$wikitext,$regs)) {
            $date = trim($regs[1]);
        } elseif (preg_match("/^\s*\|\s*ДАТАСОЗДАНИЯ\s*=(.+)$/m",$wikitext,$regs)) {
            $date = trim($regs[1]);
        }

        if (!$date) {
            if (preg_match("/\{\{О\s?тексте[^\}]+ДАТАПУБЛИКАЦИИ\s*=(.+)$/m",$wikitext,$regs)) {
                $date = trim($regs[1]);
            } elseif (preg_match("/\|\s*ДАТАПУБЛИКАЦИИ\s*=(.+)$/m",$wikitext,$regs)) {
                $date = trim($regs[1]);
            }
        }
        
        $date = TemplateExtractor::clearDate($date);        
        return $date;
    }
    
    /** Replace temlates лесенка,лесенка2 to plain text
     * 
     * {{lang|he|והיה כי יארכו הימים}}
     * {{lang-en|сезон}}
     * {{lang-it|}}
     * 
     * @param String $wikitext
     * 
     * @return String
     */
    public static function parsePoetryLadder(String $wikitext) : String
    {
        if (!$wikitext) {
            return '';
        }
        
        //$wikitext = preg_replace("/\{\{лесенка2?\s*\|(.+)(|строка=\d+)?(|№=\d+)?\}\}/iu","\\1",$wikitext);

        while (preg_match("/^(.*)\{\{лесенка2?\s*\|([^\}]+)(|строка=\d+)?(|№=\d+)?\}\}(.*)$/isu",$wikitext,$regs)) {
            $wikitext = $regs[1].preg_replace("/\|/"," ",$regs[2]).$regs[5];
        }
        return $wikitext;
    }    
    
    /** Search template {{templateName....}} in wikitext
     * and divide on three string by this template
     * Template can include other templates
     * 
     * @param String $wikitext
     * @param String $templateName
     * 
     * @return Array [string_before_template, template, string_after_template]
     */
    public static function divideByTemplate(String $wikitext, String $templateName) : Array
    {
        if (!$templateName) {
            return [$wikitext,'',''];
        }
        
        $template_start_pos = mb_stripos($wikitext,'{{'.$templateName);
        if ($template_start_pos ===false) {
            return [$wikitext,'',''];
        }
        
        $out[0] = mb_substr($wikitext,0,$template_start_pos);
        
        $count = 1;
        $offset = $template_start_pos+2;
        
        do {
            $start_pos = mb_stripos($wikitext,'{{',$offset);
            $end_pos = mb_stripos($wikitext,'}}',$offset);
            if ($end_pos !==false) {
                if ($start_pos === false || $start_pos > $end_pos) {
                    $offset = $end_pos+2; 
                    $count -=1;
                } else {
                    $offset = $start_pos+2; 
                    $count +=1;
                }
            }
        } while($count!=0 && $end_pos!==false);
        
        if ($end_pos===false) {
            $out[1] = mb_substr($wikitext,$template_start_pos);
            $out[2] = '';
        } else {
            $out[1] = mb_substr($wikitext,$template_start_pos,$end_pos-$template_start_pos+2);
            $out[2] = mb_substr($wikitext,$end_pos+2);
        }
        
        return $out;
    }    
    
}
