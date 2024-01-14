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

Auth::routes([
    'register' => false,
    'reset' => false,
    'verify' => false
]);

Route::group(['middleware' => 'web'], function () {
    Route::post('/user/save', [App\Http\Controllers\UserController::class, 'updateOrCreateUser']);
});

Route::group(['middleware' => ['auth', 'web']], function () {
    // users
    Route::get('/user/is-password-changed', [App\Http\Controllers\UserController::class, 'isUserPasswordChanged']);
    Route::post('/user/change-password', [App\Http\Controllers\UserController::class, 'changePassword']);
    Route::get('/users/get', [App\Http\Controllers\UserController::class, 'getUsers']);

    // jellyfin
    Route::post('/jellyfin/request', [App\Http\Controllers\MediaPlayerController::class, 'jellyfinRequest']);
    Route::get('/jellyfin/subtitles', [App\Http\Controllers\MediaPlayerController::class, 'getJellyfinCurrentlyPlayedSubtitles']);
    Route::post('/jellyfin/process-subtitles', [App\Http\Controllers\MediaPlayerController::class, 'processJellyfinSubtitle']);

    // vue routes
    Route::get('/dev', [App\Http\Controllers\HomeController::class, 'dev']);
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/attributions', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/patch-notes', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/admin', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/media-player', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/books', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/book/create', [App\Http\Controllers\HomeController::class, 'index']);
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
    Route::get('/language/change/{language}', [App\Http\Controllers\HomeController::class, 'changeLanguage']);
    Route::get('/language/get', [App\Http\Controllers\HomeController::class, 'getLanguage']);

    // goals
    Route::post('/goals/get', [App\Http\Controllers\GoalController::class, 'getGoals']);
    Route::post('/goal/update', [App\Http\Controllers\GoalController::class, 'updateGoal']);
    Route::post('/goals/get-calendar-data', [App\Http\Controllers\GoalController::class, 'getCalendarData']);
    Route::post('/goals/achievement/update', [App\Http\Controllers\GoalController::class, 'updateCalendarData']);

    // dictionaries
    Route::get('/jmdict/xml-to-text', [App\Http\Controllers\JmdictController::class, 'jmdictXmlToText']);

    // settings
    Route::post('/settings/get-by-name', [App\Http\Controllers\SettingsController::class, 'getSettingsByName']);
    Route::post('/settings/save', [App\Http\Controllers\SettingsController::class, 'saveSettings']);
    
    // images 
    Route::get('/images/flags/{name}', [App\Http\Controllers\ImageController::class, 'getFlagImage']);
    Route::get('/images/book_images/{name}', [App\Http\Controllers\ImageController::class, 'getBookImage']);

    // dictionary
    Route::get('/dictionaries/scan', [App\Http\Controllers\DictionaryController::class, 'getImportableDictionaryList']);
    Route::post('/dictionaries/import', [App\Http\Controllers\DictionaryController::class, 'importSupportedDictionary']);
    Route::get('/dictionaries/get-record-count/{dictionaryName}', [App\Http\Controllers\DictionaryController::class, 'getDictionaryRecordCount']);
    Route::get('/dictionaries/deepl/get-usage', [App\Http\Controllers\DictionaryController::class, 'getDeeplCharacterLimit']);
    Route::get('/dictionaries/get', [App\Http\Controllers\DictionaryController::class, 'getDictionaries']);
    Route::post('/dictionary/update', [App\Http\Controllers\DictionaryController::class, 'updateDictionary']);
    Route::post('/dictionary/search', [App\Http\Controllers\DictionaryController::class, 'searchDefinitions']);
    Route::post('/dictionary/search/inflections', [App\Http\Controllers\DictionaryController::class, 'searchInflections']);
    Route::post('/dictionary/test-csv-file', [App\Http\Controllers\DictionaryController::class, 'testDictionaryCsvFile']);
    Route::post('/dictionary/import-csv-file', [App\Http\Controllers\DictionaryController::class, 'importDictionaryCsvFile']);
    Route::get('/dictionary/delete/{dictionaryName}', [App\Http\Controllers\DictionaryController::class, 'deleteDictionary']);

    // vocabulary
    Route::get('/vocabulary/word/get/{wordId}', [App\Http\Controllers\VocabularyController::class, 'getWord']);
    Route::post('/vocabulary/word/save', [App\Http\Controllers\VocabularyController::class, 'saveWord']);
    Route::get('/vocabulary/phrase/get/{wordId}', [App\Http\Controllers\VocabularyController::class, 'getPhrase']);
    Route::post('/vocabulary/phrase/save', [App\Http\Controllers\VocabularyController::class, 'savePhrase']);
    Route::post('/vocabulary/phrase/delete', [App\Http\Controllers\VocabularyController::class, 'deletePhrase']);
    Route::post('/vocabulary/save-example-sentence', [App\Http\Controllers\VocabularyController::class, 'saveExampleSentence']);
    Route::post('/vocabulary/search', [App\Http\Controllers\VocabularyController::class, 'search']);
    Route::post('/vocabulary/export-to-csv', [App\Http\Controllers\VocabularyController::class, 'exportToCsv']);
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
    
    // anki
    Route::post('/anki/add-card', [App\Http\Controllers\AnkiController::class, 'addCardToAnki']);

    // library
    Route::post('/books', [App\Http\Controllers\BookController::class, 'getBooks']);
    Route::get('/book/get-word-counts/{bookId}', [App\Http\Controllers\BookController::class, 'getBookWordCounts']);
    Route::post('/book/save', [App\Http\Controllers\BookController::class, 'saveBook']);
    Route::post('/book/delete', [App\Http\Controllers\BookController::class, 'deleteBook']);
    Route::post('/chapters', [App\Http\Controllers\ChapterController::class, 'getChapters']);
    Route::post('/chapter/get/reader', [App\Http\Controllers\ChapterController::class, 'getChapterForReader']);
    Route::post('/chapter/delete', [App\Http\Controllers\ChapterController::class, 'deleteChapter']);
    Route::get('/chapter/get/edit/{chapterId}', [App\Http\Controllers\ChapterController::class, 'getChapterForEdit']);
    Route::post('/chapter/finish', [App\Http\Controllers\ChapterController::class, 'finishChapter']);
    Route::post('/chapter/save', [App\Http\Controllers\ChapterController::class, 'saveChapter']);

    // library import
    Route::post('/import', [App\Http\Controllers\ImportController::class, 'import']);
});