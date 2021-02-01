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

Route::get('/', 'HomeController@index');

Route::get('/privacyPolicy', 'HomeController@privacyPolicy');

Auth::routes();

Route::post('/password/reset', 'Api\ResetPasswordController@reset')->name('password.update');

Route::get('/storeSupervisor/register', 'Api\StoreSupervisorController@storeSupervisorRegister')->name('store.supervisor.register');

Route::get('/storeSupervisor/login', 'Api\StoreSupervisorController@storeSupervisorLogin')->name('store.supervisor.login');

Route::post('/storeSupervisor/register/accept', 'Api\StoreSupervisorController@addStoreSupervisorRegisterAccept')->name('store.supervisor.register.accept');

Route::post('/storeSupervisor/login/accept', 'Api\StoreSupervisorController@addStoreSupervisorLoginAccept')->name('store.supervisor.login.accept');