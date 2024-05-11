<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) { 
    return $request->user();
});


Route::namespace('App\Http\Controllers\API')->group(function() { 
   
    Route::get('push-order/{id}', 'APIController@pushOrder'); 
 
    Route::get('users/{id?}', 'APIController@getUsers'); 
   
    Route::post('add-user', 'APIController@addUser');
  
    Route::post('add-multiple-users', 'APIController@addMultipleUsers');
   
    Route::put('update-user-details/{id?}', 'APIController@updateUserDetails'); 
   
    Route::patch('update-user-name/{id?}', 'APIController@updateUserName'); 
    
    Route::delete('delete-user/{id?}', 'APIController@deleteUser'); 

    Route::delete('delete-multiple-users/{ids?}', 'APIController@deleteMultipleUsers'); 
   
    Route::post('register-user', 'APIController@registerUser');
  
    Route::post('login-user', 'APIController@loginUser');
  
    Route::post('logout-user', 'APIController@logoutUser');

    Route::post('register-user-with-passport', 'APIController@registerUserWithPassport');
   
    Route::post('login-user-with-passport', 'APIController@loginUserWithPassport');
 
    Route::post('update-stock', 'APIController@updateStock');
 
    Route::post('update-stock-with-webhook', 'APIController@updateStockWithWebhook');
});