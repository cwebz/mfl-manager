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
    return "hi"; //view('welcome');
});

Route::get('/leagues', function () {
    return view('welcome');
});

Route::get('/add-mfl-account', function () {
    return view('mfl-slack-form');
});


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::post('add-mfl-account', [
    'uses' => 'RegisterFormController@registerUser'
  ]);

