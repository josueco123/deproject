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
Route::get('/mercadolibreconfig', 'MercadoLibreController@loadMLLoginView')
->middleware(['auth', 'verified'])->name('mercadolibreconfig');
Route::get('/mercadolibreredirect', 'MercadoLibreController@redirectToMercadoLibre')
->middleware(['auth', 'verified'])->name('mercadolibreredirect');
Route::post('/mercadolibreorders', 'MercadoLibreController@getSelectMethod')
->middleware(['auth', 'verified'])->name('mercadolibreorders');
Route::get('/showproducts', 'ProductsController@index')
->middleware(['auth', 'verified'])->name('showproducts');
Route::get('/listproducts', 'ProductsController@getProducts');
Route::get('/formproduct', 'ProductsController@create')
->middleware(['auth', 'verified'])->name('formproduct');
Route::post('/saveproduct', 'ProductsController@store')
->middleware(['auth', 'verified'])->name('saveproduct');
Route::get('/editproduct/{id}', 'ProductsController@edit')
->middleware(['auth', 'verified'])->name('editproduct');
Route::post('/updateproduct/{id}', 'ProductsController@update')
->middleware(['auth', 'verified'])->name('updateproduct');

Route::get('/clear-cache', function () {
    echo Artisan::call('config:clear');
    echo Artisan::call('config:cache');
    echo Artisan::call('cache:clear');
    echo Artisan::call('route:clear');
 });
 