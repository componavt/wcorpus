<?php

namespace Wcorpus\Models;

use Illuminate\Database\Eloquent\Model;

use componavt\phpMorphy\Morphy;
//require_once(dirname(__FILE__) . '/../../vendor/componavt/phpmorphy/libs/phpmorphy/src/common.php');

class Lemma extends Model
{
    protected $fillable = ['lemma','pos_id','dictionary','freq'];

    // Lemma __belongs_to__ PartOfSpeech
    // $pos_name = PartOfSpeech::find(9)->name_ru;
    public function pos()
    {
        return $this->belongsTo(POS::class,'pos_id');
    }
    

    public static function lemmatize($word)
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
            $morphy = new Morphy('ru');
            //echo $morphy->getPseudoRoot('FIGHTY');

        } catch(phpMorphy_Exception $e) {
            die('Error occured while creating phpMorphy instance: ' . PHP_EOL . $e);
        }
            
        if (!$word) {
            return '';
        }
            
        // $morphy = new Morphy('ru');
        // $lemma = $morphy->getPseudoRoot($word);
        $word = mb_strtoupper($word);
        $lemma = $morphy->lemmatize($word);
            
        // $lemma = "some text";
            //$lemma=Morphy::getPseudoRoot($word);
            
        return $lemma;
    }
}
