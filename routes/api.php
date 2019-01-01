<?php

use Illuminate\Http\Request;

/**
 * Version 1 
 */
Route::prefix('v1')->group(function () {
    Route::post('register', 'API\DiscoveryController@register');
    Route::post('unregister', 'API\DiscoveryController@unregister');
    Route::get('services', 'API\DiscoveryController@index');
});


