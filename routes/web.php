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
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/disclaimer', 'Frontend\DisclaimerController@index')->name('disclaimer');

// User routes 
Route::get('/admin/gebruikers', 'Backend\UsersController@index')->name('admin.users.index');
Route::get('/admin/gebruikers/nieuw', 'Backend\UsersController@create')->name('admin.users.create');
Route::get('/admin/gebruikers/verwijder/{id}', 'Backend\UsersController@destroy')->name('admin.users.destroy');
Route::post('/admin/gebruikers/opslaan', 'Backend\UsersController@store')->name('admin.users.save');

// Ban routes 
Route::get('/admin/gebruiker/blokkeer/{id}', 'Backend\BanController@lock')->name('admin.users.lock');
Route::get('/admin/gebruiker/activeer/{id}', 'Backend\BanController@unlock')->name('admin.users.unlock');