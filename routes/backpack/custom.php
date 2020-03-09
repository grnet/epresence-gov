<?php

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes


    Route::get('language_files','LanguageFileController@index')->name('manage_language_files');
    Route::post('export_language_files','LanguageFileController@export');
    Route::post('update_string','LanguageFileController@update_language_line');

    CRUD::resource('downloads', 'DownloadCrudController');

    CRUD::resource('videos', 'VideoCrudController');

    CRUD::resource('documents', 'DocumentCrudController');

    CRUD::resource('faq', 'FaqCrudController');

    CRUD::resource('emails', 'EmailCrudController');

    CRUD::resource('notifications', 'NotificationCrudController');



}); // this should be the absolute last line of this file
