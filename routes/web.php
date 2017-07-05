<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    //return view('welcome');
    return view('home');
});

Auth::routes();

Route::get('/home', 'HomeController@index');
Route::get('/stats', 'HomeController@stats');

Route::get('/author/name_list', 'AuthorController@namesList');

Route::get('/lemma/count_wordforms','LemmaController@countWordforms');
Route::get('/lemma/link_ruwikt','LemmaController@linkRuWikt');

Route::get('/sentence/{id}/break_into_words','SentenceController@breakSentence');
Route::get('/sentence/break_sentences','SentenceController@breakAllSentences');

Route::get('/text/extractFromWikiSource','TextController@extractFromWikiSource');
Route::get('/text/{id}/parseWikitext','TextController@parseWikitext');
Route::get('/text/parseAllWikitext','TextController@parseAllWikitext');
Route::get('/text/templateStats','TextController@templateStats');
Route::get('/text/parse_text','TextController@parseText');
Route::get('/text/sentences_to_file','TextController@sentencesToFile');

Route::get('/text/{id}/break_into_sentences','TextController@breakText');
Route::get('/text/break_texts','TextController@breakAllTexts');

Route::get('/text/title_list', 'TextController@titlesList');

Route::get('/wordform/{id}/lemmatize','WordformController@lemmatize');
Route::get('/wordform/lemmatize_all','WordformController@lemmatizeAll');
Route::get('/wordform/delete_bad_wordforms','WordformController@deleteBadWordforms');
Route::get('/wordform/delete_apostrophs','WordformController@deleteWordsWithApostroph');
Route::get('/wordform/count_sentences','WordformController@countSentences');

Route::resource('/lemma', 'LemmaController',
                ['names' => ['update' => 'lemma.update',
                             'store' => 'lemma.store',
                             'destroy' => 'lemma.destroy']]);

Route::resource('/pos', 'POSController',
                ['names' => ['update' => 'pos.update',
                             'store' => 'pos.store',
                             'destroy' => 'pos.destroy']]);

Route::resource('/sentence', 'SentenceController',
                ['names' => ['update' => 'sentence.update',
                             'store' => 'sentence.store',
                             'destroy' => 'sentence.destroy']]);

Route::resource('/text', 'TextController',
                ['names' => ['update' => 'text.update',
                             'store' => 'text.store',
                             'destroy' => 'text.destroy']]);

Route::resource('/wordform', 'WordformController',
                ['names' => ['update' => 'wordform.update',
                             'store' => 'wordform.store',
                             'destroy' => 'wordform.destroy']]);

Route::get('/piwidict/relation_type', 'Piwidict\RelationTypeController@index');
