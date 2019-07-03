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


/// test routes
Route::get('/test', 'ParseController@test');
Route::get('/test-usa', 'ParseController@testUSA');
Route::get('/test-google', 'ParseController@testTranslate');


/// dev routes
Route::get('/sitemap.xml', 'SitemapController@index');
Route::post('/common_search', 'SearchController@commonSearch');


Route::get('/', 'ParseController@index');
Route::get('/msfo_consultation', 'TranslateController@index');
Route::get('/buhgalterskaya_novost/{slug}', 'ParseController@show_news');
Route::get('/buhgalterskaya_consultacia/{slug}', 'TranslateController@show_article');
Route::get('/sourse/{slug}', 'SearchController@sourseSearch');

Route::fallback(function(){
    return response()->view('errors.404', [], 404);
});


















