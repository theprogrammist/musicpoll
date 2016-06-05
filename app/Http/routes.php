<?php

Route::get('auth',[
    'uses'=>'Auth\AuthController@adduser'
]);

Route::group(['prefix' => 'api/v1','middleware' => 'auth:api'], function($router)
{
    Route::resource('vote', 'VoteController');
});
