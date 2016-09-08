<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('login', function () {
    return view('login', ['isLoginPage' => true]);
});

Route::get('qqlogin', 'LoginController@qqLogin');
Route::get('qqlogin_callback', 'LoginController@qqCallback');
Route::get('qqlogout', 'LoginController@qqLogout');

Route::group(['middleware' => 'auth'], function() {
    Route::get('/', 'InvitationController@index');
    Route::get('getimage/{filename}', 'InvitationController@getImage');
    Route::post('addimages', 'InvitationController@addImages');
    Route::delete('deleteimage/{filename}', 'InvitationController@deleteImage');
    Route::post('save', 'InvitationController@save');
    Route::post('publish', 'InvitationController@publish');
    Route::post('cancelpublish', 'InvitationController@cancelPublish');
    Route::post('remodify', 'InvitationController@reModify');
    Route::get('review', 'InvitationController@review');
    Route::get('help', 'InvitationController@help');
    Route::post('sendsuggest', 'MessageController@sendSuggest');
    Route::get('showmessages', 'MessageController@showPage');
    Route::post('fetchmessages/{pageIndex}/{pageSize}', 'MessageController@fetchMessages');
    Route::post('setreadmessage/{id}', 'MessageController@setRead');
});

Route::get('admin', 'AdminController@login');
Route::get('admin/logout', 'AdminController@logout');
Route::group(['middleware' => 'admin_auth'], function() {
    Route::get('admin/show', 'AdminController@showSpecialStateInvitations');
    Route::get('getimage/{invitationid}/{filename}', 'AdminController@getImage');
    Route::post('getImagesById/{id}', 'AdminController@getImagesUrlById');
    Route::post('batchChangeStates', 'AdminController@changeStates');
    Route::get('admin/showmessage', 'AdminController@showMessage');
    Route::post('admin/fetchmessages/{pageIndex}/{pageSize}', 'AdminController@fetchMessages');
    Route::post('admin/setreadmessage/{id}', 'MessageController@setRead');
});

Route::get('invitationInfo/{invitationid}', 'CustomController@getInvitationInfo');
Route::get('custom/getimage/{invitationid}/{filename}', 'CustomController@getImage');
Route::get('custom/addcomment/{invitationId}', 'CustomController@addComment');