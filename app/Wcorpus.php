<?php
/* Wcorpus.php - set of general useful functions.
 * 
 * Copyright (c) 2013 Andrew Krizhanovsky <andrew.krizhanovsky at gmail.com>
 * Distributed under EPL/LGPL/GPL/AL/BSD multi-license.
 */
namespace Wcorpus;

use componavt\phpMorphy\Morphy;
//require_once(dirname(__FILE__) . '/../../vendor/componavt/phpmorphy/libs/phpmorphy/src/common.php');

class Wcorpus
{
    /* phpMorphy instance */
    private static $morphy;

    /** Takes data from search form (title, language) and 
     * returns string for url such_as 
     * title=$title&lang_id=$lang_id
     * IF value is empty, the pair 'argument-value' is ignored
     * 
     * @param Array $url_args - array of pairs 'argument-value', f.e. ['title'=>'...', lang_id=>1]
     * @return String f.e. 'pos_id=11&lang_id=1'
     */
    public static function searchValuesByURL(Array $url_args=NULL) : String
    {
/*        $url = '';
        if (isset($url_args) && sizeof($url_args)) {
            $tmp=[];
            foreach ($url_args as $a=>$v) {
                if ($v!='' && !($a=='page' && $v==1) && !($a=='limit_num' && $v==10)) {
                    $tmp[] = "$a=$v";
                }
            }
            if (sizeof ($tmp)) {
                $url .= "?".implode('&',$tmp);
            }
        }
        
        return $url; */
        return http_build_query($url_args);
    }
    
    /** Разбивает длинную строку без пробелов на равные отрезки, 
     * разделенные пробелами 
     * 
     * @param String $text - long text
     * @param Integer $length - length of substring
     * 
     * @return String
     */
    public static function stringChunk(String $text, Integer $length): String
    {
        
    }
    
    public static function initMorphy() 
    {
        // set some options
/*  $opts = array(
        // storage type, follow types supported
        // PHPMORPHY_STORAGE_FILE - use file operations(fread, fseek) for dictionary access, this is very slow...
        // PHPMORPHY_STORAGE_SHM - load dictionary in shared memory(using shmop php extension), this is preferred mode
        // PHPMORPHY_STORAGE_MEM - load dict to memory each time when phpMorphy intialized, this useful when shmop ext. not activated. Speed same as for PHPMORPHY_STORAGE_SHM type
        
        //'storage' => 'file', //PHPMORPHY_STORAGE_FILE,
        //'storage' => PHPMORPHY_STORAGE_FILE,
        'storage' => phpMorphy::STORAGE_FILE,
        
        // Enable prediction by suffix
        'predict_by_suffix' => true, 
        // Enable prediction by prefix
        'predict_by_db' => true,
        // TODO: comment this
        'graminfo_as_text' => true,
    );
 */
        // Path to directory where dictionaries located
        #$dir = dirname(__FILE__) . '/../dicts';
        $dir = dirname(__FILE__) . '/vendor/componavt/phpmorphy/libs/phpmorphy/dicts';
        $lang = 'ru_RU';

        // Create phpMorphy instance
        try {
            // $morphy = new phpMorphy($dir, $lang, $opts);

            //$morphy = new componavt\phpMorphy\Morphy('en');
            self::$morphy = new Morphy('ru');
            //echo $morphy->getPseudoRoot('FIGHTY');

        } catch(phpMorphy_Exception $e) {
            die('Error occured while creating phpMorphy instance: ' . PHP_EOL . $e);
        }
        
        
        return self::$morphy;
    }
    
    public static function getMorphy() 
    {
        if (self::$morphy) {
            return self::$morphy;
        } else {
            return self::initMorphy();
        }

    }

}
