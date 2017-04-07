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

Route::get('/text/extractFromWikiSource','TextController@extractFromWikiSource');
Route::get('/text/parseWikitext','TextController@parseWikitext');
Route::get('/text/templateStats','TextController@templateStats');

Route::resource('/text', 'TextController',
                ['names' => ['update' => 'text.update',
                             'store' => 'text.store',
                             'destroy' => 'text.destroy']]);
