<?php
Route::group([
    'middleware' => config('selenium_ide_manager.middleware'),
    'prefix' => 'selenium-ide-manager',
    'namespace' => 'Plum\SeleniumIdeManager\Http\Controllers'
], function () {
    Route::name('selenium-ide-manager.')->group(function () {
        Route::get('/', 'IndexController@index')->name('index');
        Route::resource('suite', 'IndexController', [
            'parameters' => ['suite' => 'id']
        ])->except([
            'show',
            'update',
            'edit'
        ]);
        Route::get('/suite/change-status/{id}', 'IndexController@changeStatus')->name('change-status');
        Route::put('/test-case/{id}', 'TestCaseController@update')->name('test-case.update');
        Route::get('/test-case/{id}', 'TestCaseController@show')->name('test-case.show');
        Route::get('/export', 'ExportController@index')->name('export.index');
        Route::get('/config/{id}', 'SuiteConfigController@edit')->name('config.edit');
        Route::put('/config/{id}', 'SuiteConfigController@update')->name('config.update');
    });
});
