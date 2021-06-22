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

Route::redirect('/', '/index.html#/home');

Route::prefix('download')->group(function () {
    Route::get('/', 'Web\DownloadController@index');
    Route::get('/plist.xml', 'Web\DownloadController@plist');
});

Route::get('walletMiddle/GetDrawAddress', 'WalletController@GetDrawAddress');
Route::post('walletMiddle/BindDrawAddress', 'WalletController@BindDrawAddress');
Route::get('walletMiddle/SendVerificationcode', 'WalletController@SendVerificationcode');