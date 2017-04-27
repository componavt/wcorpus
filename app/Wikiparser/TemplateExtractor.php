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
    
    /**
     * Split a text into paragraphs
     *
     * @param $text String text 
     * @return Array collection of paragraphs
     */
    public static function splitIntoParagraphs($text): Array
    {
        $paragraphs = [];
        $text = trim($text);
        
        if (!$text) {
            return $paragraphs;
        }
/*        
        $text = str_replace(chr(13),'',$text);
        $paragraphs = explode("\n\n",$text);
 * 
 */
        
/*  
        if (preg_match_all("/(\n|^)([^\n]+)(?=\n|$)/s",$text,$regs, PREG_PATTERN_ORDER)) {
            foreach ($regs[2] as $reg) {
                   $reg = trim($reg);
                   if ($reg) {
                        $paragraphs[] = $reg;
                   }
            }
        } else {
            $paragraphs[] = $text;
        }
 * 
 */
        
/*
        $text = preg_replace("/\r\n/u","\n",$text);
        $text = preg_replace("/\r/u","\n",$text);
        $paragraphs = preg_split("/\n{2,}/su",$text);
*/
        $text = nl2br($text);
        $text = preg_replace("/\<br \/\>\s*/u","\n",$text);
        $paragraphs = explode("\n\n",$text);
            
//print_r($paragraphs);
        return $paragraphs;
    }    
    
    /**
     * Split a paragraph into sentences
     * Punctuation marks are discarded
     *
     * @param $text String text 
     * @return Array collection of sentences
     */
    public static function splitIntoSentences($text): Array
    {
        $sentences = [];
        $text = trim($text);

        if (!$text) {
            return $sentences;
        }
        
        $text = preg_replace("/\n/",' ',$text);
        
        if (preg_match_all("/((\d+\.\s*)*[А-ЯA-Z]((т.п.|т.д.|пр.|g.)|[^?!.\(]|\([^\)]*\))*[.?!])/u",$text,$regs, PREG_PATTERN_ORDER)) {
            $sentences = $regs[0];
        } else {
            $sentences[] = $text;
        }
        
/*
        $sen_count = 1;
        $word_count = 1;

        $end1 = ['.','?','!','…'];
        $end2 = ['.»','?»','!»','."','?"','!"','.”','?”','!”'];
        $pseudo_end = false;
        if (!in_array(mb_substr($text,-1,1),$end1) && !in_array(mb_substr($text,-1,2),$end2)) {
            $text .= '.';
            $pseudo_end = true;
        }

        if (preg_match_all("/(.+?)(\.|\?|!|\.»|\?»|!»|\.\"|\?\"|!\"|\.”|\?”|!”|…{1,})(\s|(<br(| \/)>\s*){1,}|$)/is", // :|
                           $text, $desc_out)) {
            for ($k=0; $k<sizeof($desc_out[1]); $k++) {
                $sentence = trim($desc_out[1][$k]);

                // <br> in in the beginning of the string is moved before the sentence
                if (preg_match("/^(<br(| \/)>)(.+)$/is",$sentence,$regs)) {
                    $sentence = trim($regs[3]);
                }

                $sentences[] = str_replace("<br \>\n",'',$sentence);
            }
        }
*/
        return $sentences;
    }    
    
    /**
     * Split a sentence into words without punctuation marks
     *
     * @param $text String text 
     * @return Array collection of words
     */
    public static function splitIntoWords($text): Array
    {
        $words = [];
        $text = trim($text);

        if (!$text) {
            return $words;
        }
        
        if (preg_match_all("/(([[:alpha:]]+['-])*[[:alpha:]]+'?)/u",$text,$regs, PREG_PATTERN_ORDER)) {
            $words = $regs[0];
        } else {
            $words[] = $text;
        }

        return $words;
    }
    
    // \\p{P}?[ \\t\\n\\r]+
    /**
     * Divides sentence on words
     *
     * @param $sentence String text without mark up
     * @param $word_count Integer initial word count
     *
     * @return Array text with markup (split to words) and next word count
     */
/*    
    public static function markupSentence($sentence,$word_count): Array
    {
        $delimeters = ',.!?"[](){}«»=”:,'; // - and ' - part of word
        // different types of dashes and hyphens: '-', '‒', '–', '—', '―' 
        // if dashes inside words, then they are part of words,
        // if dashes surrounded by spaces, then dashes are not parts of words.
        $dashes = '-‒–—―';
        
        $str = '';
        $i = 0;
        $is_word = false; // word tag <w> is not opened
        $token = $sentence;
        while ($i<mb_strlen($token)) {
            $char = mb_substr($token,$i,1);
            if (mb_strpos($delimeters, $char) || preg_match("/\s/",$char)) {
                if ($is_word) {
                    $str .= '</w>';
                    $is_word = false;
                }
                $str .= $char;
            } elseif ($char == '<') { // && strpos($token,'>',$i+1)
                if ($is_word) {
                    $str .= '</w>';
                    $is_word = false;
                }
                $j = mb_strpos($token,'>',$i+1);
                $str .= mb_substr($token,$i,$j-$i+1);
                $i = $j;
            } else {
                $next_char = ($i+1 < mb_strlen($token)) ? mb_substr($token,$i+1,1) : '';
                $next_char_is_special = (!$next_char || mb_strpos($delimeters, $next_char) || preg_match("/\s/",$next_char) || mb_strpos($dashes,$next_char));
//                if (!$is_word && !preg_match("/^-\s/",mb_substr($token,$i,2))) {
                if (!$is_word && !(mb_strpos($dashes,$char)!==false && $next_char_is_special)) { // && $next_char_is_special
                    $str .= '<w id="'.$word_count++.'">';
                    $is_word = true;
                }
                $str .= $char;
            }
            $i++;
        }
        if ($is_word) {
            $str .= '</w>';
        }
        return [$str,$word_count];
    }
*/
}
