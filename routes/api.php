<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/
Route::get('main-products', 'Api\ProductoController@main_products');
Route::get('new-products', 'Api\ProductoController@new_products');
Route::get('discounts', 'Api\ProductoController@discounts');
Route::get('legacy', 'Api\ProductoController@legacy');
Route::post('ofertas', 'Api\OfertasController@ofertas_rata');
Route::get('producto/{uuid}', 'Api\ProductoController@getByUuid');
Route::post('producto/search', 'Api\ProductoController@search');
Route::group(['prefix' => 'auth',], function () {
    Route::post('login', 'Api\AuthController@login');
    Route::post('logout', 'Api\AuthController@logout');
    Route::post('refresh', 'Api\AuthController@refresh');
    Route::post('me', 'Api\AuthController@me');
});
Route::group(['middleware' => ['jwt'], 'prefix' => 'admin'], function(){
  Route::apiResource('tienda', 'Api\TiendaController');
  Route::get('producto/{id}/toggle-status', 'Api\ProductoController@toggleProducto');
  Route::apiResource('producto', 'Api\ProductoController');
  Route::get('ofertas', 'Api\OfertasController@index');
});


Route::get('update_product/{id}', 'Backend\TestController@checkProduct');
