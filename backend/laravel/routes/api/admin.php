<?php

use App\Http\Controllers\Dictionaries\DictionaryController;
use App\Http\Controllers\Dictionaries\DictionaryImportController;
use App\Http\Controllers\Fonts\FontTypeController;
use App\Http\Controllers\Languages\LanguageController;
use App\Http\Controllers\Settings\GlobalSettingsController;
use App\Http\Controllers\Users\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('/dictionaries')->group(function () {
    // not used since 0.1 release, left it here if needed in future
    // Route::get('/jmdict/xml-to-text', [DictionaryImportService::class, 'jmdictXmlToText']);

    Route::get('/deepl/usage', [DictionaryController::class, 'deeplUsage']);

    Route::prefix('/import')->group(function () {
        Route::post('/validate', [DictionaryImportController::class, 'validate']);
        Route::post('/csv/validate', [DictionaryImportController::class, 'validateCsv']);
        Route::post('/csv', [DictionaryImportController::class, 'importCsv']);
        Route::post('/', [DictionaryImportController::class, 'import']);
    });

    Route::get('/', [DictionaryController::class, 'index']);
    Route::get('/{dictionary}', [DictionaryController::class, 'show']);
    Route::post('/store-api', [DictionaryController::class, 'storeApi']);
    Route::post('/{dictionary}', [DictionaryController::class, 'update']);
    Route::delete('/{dictionary}', [DictionaryController::class, 'destroy']);
});

Route::prefix('/fonts')->group(function () {
    Route::get('/', [FontTypeController::class, 'index']);
    Route::post('/store', [FontTypeController::class, 'store']);
    Route::post('/{fontType}', [FontTypeController::class, 'update']);
    Route::delete('/{fontType}', [FontTypeController::class, 'destroy']);
});

Route::prefix('/languages')->group(function () {
    Route::get('/', [LanguageController::class, 'indexInstallRequired']);
    Route::get('/installed', [LanguageController::class, 'indexInstalled']);
    Route::post('/install', [LanguageController::class, 'install']);
    Route::delete('/', [LanguageController::class, 'uninstall']);
});

Route::prefix('/settings')->group(function () {
    Route::post('/', [GlobalSettingsController::class, 'show']);
    Route::post('/update', [GlobalSettingsController::class, 'update']);
});

Route::prefix('/users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/{user}', [UserController::class, 'update']);
});
