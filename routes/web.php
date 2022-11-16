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

Auth::routes();

Route::group(['middleware' => 'web'], function () {
    
    // vue routes
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/books', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/books/create', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/chapters/{id}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/chapters/read/{id}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/chapters/create/{bookId}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/chapters/edit/{bookId}/{chapterId}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/flashcards', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/flashcards/edit/{flashcardCollectionId?}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/review/{bookId?}/{chapterId?}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/vocabulary/search', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/vocabulary/search/{text}/{stage}/{book}/{chapter}/{translation}/{phrases}/{orderBy}/{page}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/kanji/search', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/kanji/{character}', [App\Http\Controllers\HomeController::class, 'index']);

    
    // home
    Route::post('/statistics', [App\Http\Controllers\HomeController::class, 'statistics']);
    Route::get('/language/{language}', [App\Http\Controllers\HomeController::class, 'changeLanguage']);
    Route::get('/dev', [App\Http\Controllers\HomeController::class, 'dev']);

    // tools
    Route::get('/jmdict/text-generator', [App\Http\Controllers\ToolController::class, 'jmdictTextGenerator']);
    Route::get('/jmdict/import', [App\Http\Controllers\ToolController::class, 'jmdictImport']);
    Route::get('/kanji/import', [App\Http\Controllers\ToolController::class, 'kanjiImport']);
    
    // images 
    Route::get('/images/flags/{name}', [App\Http\Controllers\ImageController::class, 'getFlagImage']);
    Route::get('/images/book_images/{name}', [App\Http\Controllers\ImageController::class, 'getBookImage']);

    // dictionary
    Route::post('/dictionary/search', [App\Http\Controllers\DictionaryController::class, 'search']);
    Route::post('/dictionary/search/inflections', [App\Http\Controllers\DictionaryController::class, 'searchInflections']);

    // vocabulary
    Route::post('/vocabulary/search', [App\Http\Controllers\VocabularyController::class, 'search']);
    Route::post('/kanji/search', [App\Http\Controllers\VocabularyController::class, 'searchKanji']);
    Route::post('/kanji/details', [App\Http\Controllers\VocabularyController::class, 'getKanjiDetails']);

    
    // review
    Route::post('/review', [App\Http\Controllers\ReviewController::class, 'review']);
    Route::post('/review/finish', [App\Http\Controllers\ReviewController::class, 'finishReview']);

    // flash cards
    Route::post('/flashcards', [App\Http\Controllers\FlashcardController::class, 'getFlashcardCollections']);
    Route::post('/flashcards/delete', [App\Http\Controllers\FlashcardController::class, 'deleteFlashcardCollection']);
    Route::post('/flashcards/get', [App\Http\Controllers\FlashcardController::class, 'getFlashcardCollection']);
    Route::post('/flashcards/save', [App\Http\Controllers\FlashcardController::class, 'saveFlashcardCollection']);
    
    // books
    Route::post('/books', [App\Http\Controllers\BookController::class, 'getBooks']);
    Route::post('/books/create', [App\Http\Controllers\BookController::class, 'createBook']);

    // chapters
    Route::post('/chapters', [App\Http\Controllers\ChapterController::class, 'getChapters']);
    Route::post('/chapter/get/reader', [App\Http\Controllers\ChapterController::class, 'getChapterForReader']);
    Route::post('/chapter/get/edit', [App\Http\Controllers\ChapterController::class, 'getChapterForEdit']);
    Route::post('/chapter/finish', [App\Http\Controllers\ChapterController::class, 'finishChapter']);
    Route::post('/chapter/save', [App\Http\Controllers\ChapterController::class, 'saveChapter']);
});