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
    
    //jellyfin
    Route::post('/jellyfin/request', [App\Http\Controllers\MediaPlayerController::class, 'jellyfinRequest']);
    Route::get('/jellyfin/subtitles', [App\Http\Controllers\MediaPlayerController::class, 'getJellyfinCurrentlyPlayedSubtitles']);
    Route::post('/jellyfin/process-subtitles', [App\Http\Controllers\MediaPlayerController::class, 'processJellyfinSubtitle']);

    // vue routes
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/admin', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/media-player', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/books', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/books/create', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/chapters/{id}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/chapters/read/{id}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/chapters/create/{bookId}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/chapters/edit/{bookId}/{chapterId}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/flashcards', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/flashcards/edit/{flashcardCollectionId?}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/review/{practiceMode?}/{bookId?}/{chapterId?}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/vocabulary/search', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/vocabulary/search/{text}/{stage}/{book}/{chapter}/{translation}/{phrases}/{orderBy}/{page}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/kanji/search', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/kanji/{character}', [App\Http\Controllers\HomeController::class, 'index']);

    
    // home
    Route::post('/statistics/get', [App\Http\Controllers\HomeController::class, 'getStatistics']);
    Route::post('/goals/get', [App\Http\Controllers\HomeController::class, 'getGoals']);
    Route::post('/get-calendar-data', [App\Http\Controllers\HomeController::class, 'getCalendarData']);
    Route::get('/language/change/{language}', [App\Http\Controllers\HomeController::class, 'changeLanguage']);
    Route::get('/language/get', [App\Http\Controllers\HomeController::class, 'getLanguage']);

    // tools
    Route::get('/tools/jmdict/text-generator', [App\Http\Controllers\ToolController::class, 'jmdictTextGenerator']);
    Route::get('/tools/jmdict/import', [App\Http\Controllers\ToolController::class, 'jmdictImport']);
    Route::get('/tools/kanji/import', [App\Http\Controllers\ToolController::class, 'kanjiImport']);
    Route::get('/tools/radicals/import', [App\Http\Controllers\ToolController::class, 'kanjiRadicalImport']);
    
    // images 
    Route::get('/images/flags/{name}', [App\Http\Controllers\ImageController::class, 'getFlagImage']);
    Route::get('/images/book_images/{name}', [App\Http\Controllers\ImageController::class, 'getBookImage']);

    // dictionary
    Route::post('/dictionary/search', [App\Http\Controllers\DictionaryController::class, 'search']);
    Route::post('/dictionary/search/inflections', [App\Http\Controllers\DictionaryController::class, 'searchInflections']);

    // vocabulary
    Route::get('/vocabulary/word/get/{wordId}', [App\Http\Controllers\VocabularyController::class, 'getWord']);
    Route::post('/vocabulary/word/save', [App\Http\Controllers\VocabularyController::class, 'saveWord']);
    Route::get('/vocabulary/phrase/get/{wordId}', [App\Http\Controllers\VocabularyController::class, 'getPhrase']);
    Route::post('/vocabulary/phrase/save', [App\Http\Controllers\VocabularyController::class, 'savePhrase']);
    Route::post('/vocabulary/phrase/delete', [App\Http\Controllers\VocabularyController::class, 'deletePhrase']);
    Route::post('/vocabulary/save-example-sentence', [App\Http\Controllers\VocabularyController::class, 'saveExampleSentence']);
    Route::post('/vocabulary/search', [App\Http\Controllers\VocabularyController::class, 'search']);
    Route::get('/vocabulary/example-sentence/{targetId}/{targetType}', [App\Http\Controllers\VocabularyController::class, 'getExampleSentence']);
    Route::post('/kanji/search', [App\Http\Controllers\VocabularyController::class, 'searchKanji']);
    Route::post('/kanji/details', [App\Http\Controllers\VocabularyController::class, 'getKanjiDetails']);
    
    // review
    Route::post('/review', [App\Http\Controllers\ReviewController::class, 'review']);
    Route::post('/review/update', [App\Http\Controllers\ReviewController::class, 'updateReviewCounts']);

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