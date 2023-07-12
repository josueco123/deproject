<?php

use Illuminate\Support\Facades\Route;

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
Route::get('/uploadthird', 'ReportsController@loadUploadThirdsView')
->middleware(['auth', 'verified'])->name('loadUploadThirds');
Route::post('/uploadthird', 'ReportsController@getDataToImportML')
->middleware(['auth', 'verified'])->name('sendUploadThirds');
Route::get('/uploadbilling', 'ReportsController@loadUploadBillingView')
->middleware(['auth', 'verified'])->name('loadUploadBilling');
Route::post('/uploadbilling', 'ReportsController@getDataToImportBillML')
->middleware(['auth', 'verified'])->name('sendUploadBilling');
Route::get('/accessdmml', 'MercadoLibreController@handleMercadoLibreCallback')
->middleware(['auth', 'verified'])->name('accessdmml');
Route::get('/mercadolibreapi', 'MercadoLibreController@loadMLView')
->middleware(['auth', 'verified'])->name('mercadolibreapi');

Route::get('/clear-cache', function () {
    echo Artisan::call('config:clear');
    echo Artisan::call('config:cache');
    echo Artisan::call('cache:clear');
    echo Artisan::call('route:clear');
 });
 