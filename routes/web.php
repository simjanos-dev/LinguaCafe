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
    Route::get('/dev', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/attributions', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/patch-notes', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/admin', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/media-player', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/books/{bookId?}', [App\Http\Controllers\HomeController::class, 'index']);
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
    Route::get('/config/get/{configPath}', [App\Http\Controllers\HomeController::class, 'getConfig']);

    // goals
    Route::post('/goals/get', [App\Http\Controllers\GoalController::class, 'getGoals']);
    Route::post('/goal/update', [App\Http\Controllers\GoalController::class, 'updateGoal']);
    Route::post('/goals/get-calendar-data', [App\Http\Controllers\GoalController::class, 'getCalendarData']);
    Route::post('/goals/achievement/update', [App\Http\Controllers\GoalController::class, 'updateCalendarData']);
    Route::get ('/goals/achievement/review/update', [App\Http\Controllers\GoalController::class, 'updateReviewGoalAchievement']);

    // dictionaries
    Route::get('/jmdict/xml-to-text', [App\Http\Controllers\JmdictController::class, 'jmdictXmlToText']);

    // settings
    Route::post('/settings/get-by-name', [App\Http\Controllers\SettingsController::class, 'getSettingsByName']);
    Route::post('/settings/save', [App\Http\Controllers\SettingsController::class, 'saveSettings']);
    
    // images 
    Route::get('/images/book_images/{name}', [App\Http\Controllers\ImageController::class, 'getBookImage']);

    // dictionary
    Route::get('/dictionaries/scan', [App\Http\Controllers\DictionaryController::class, 'getImportableDictionaryList']);
    Route::post('/dictionaries/import', [App\Http\Controllers\DictionaryController::class, 'importSupportedDictionary']);
    Route::get('/dictionaries/get-record-count/{dictionaryName}', [App\Http\Controllers\DictionaryController::class, 'getDictionaryRecordCount']);
    Route::get('/dictionaries/deepl/get-usage', [App\Http\Controllers\DictionaryController::class, 'getDeeplCharacterLimit']);
    Route::post('/dictionaries/deepl/search', [App\Http\Controllers\DictionaryController::class, 'searchDeepl']);
    Route::get('/dictionaries/get', [App\Http\Controllers\DictionaryController::class, 'getDictionaries']);
    Route::post('/dictionary/update', [App\Http\Controllers\DictionaryController::class, 'updateDictionary']);
    Route::post('/dictionary/search', [App\Http\Controllers\DictionaryController::class, 'searchDefinitions']);
    Route::post('/dictionary/search-for-hover-vocabulary', [App\Http\Controllers\DictionaryController::class, 'searchDefinitionsForHoverVocabulary']);
    Route::post('/dictionary/search/inflections', [App\Http\Controllers\DictionaryController::class, 'searchInflections']);
    Route::post('/dictionary/test-csv-file', [App\Http\Controllers\DictionaryController::class, 'testDictionaryCsvFile']);
    Route::post('/dictionary/import-csv-file', [App\Http\Controllers\DictionaryController::class, 'importDictionaryCsvFile']);
    Route::get('/dictionary/delete/{dictionaryName}', [App\Http\Controllers\DictionaryController::class, 'deleteDictionary']);

    // vocabulary
    Route::get ('/vocabulary/words/get/{wordId}', [App\Http\Controllers\VocabularyController::class, 'getUniqueWord']);
    Route::post('/vocabulary/word/update', [App\Http\Controllers\VocabularyController::class, 'updateWord']);
    Route::get ('/vocabulary/phrases/get/{phraseId}', [App\Http\Controllers\VocabularyController::class, 'getPhrase']);
    Route::post('/vocabulary/phrases/create', [App\Http\Controllers\VocabularyController::class, 'createPhrase']);
    Route::post('/vocabulary/phrases/update', [App\Http\Controllers\VocabularyController::class, 'updatePhrase']);
    Route::post('/vocabulary/phrases/delete', [App\Http\Controllers\VocabularyController::class, 'deletePhrase']);
    Route::post('/vocabulary/example-sentence/create-or-update', [App\Http\Controllers\VocabularyController::class, 'createOrUpdateExampleSentence']);
    Route::post('/vocabulary/search', [App\Http\Controllers\VocabularyController::class, 'search']);
    Route::post('/vocabulary/export-to-csv', [App\Http\Controllers\VocabularyController::class, 'exportToCsv']);
    Route::get ('/vocabulary/example-sentence/{targetType}/{targetId}', [App\Http\Controllers\VocabularyController::class, 'getExampleSentence']);
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

    // books
    Route::post('/books', [App\Http\Controllers\BookController::class, 'getBooks']);
    Route::get ('/books/get-word-counts/{bookId}', [App\Http\Controllers\BookController::class, 'getBookWordCounts']);
    Route::post('/books/create', [App\Http\Controllers\BookController::class, 'createBook']);
    Route::post('/books/update', [App\Http\Controllers\BookController::class, 'updateBook']);
    Route::post('/books/delete', [App\Http\Controllers\BookController::class, 'deleteBook']);
    
    // chapters
    Route::post('/chapters', [App\Http\Controllers\ChapterController::class, 'getChaptersForBook']);
    Route::post('/chapters/get/reader', [App\Http\Controllers\ChapterController::class, 'getChapterForReader']);
    Route::post('/chapters/get/editor', [App\Http\Controllers\ChapterController::class, 'getChapterForEditor']);
    Route::post('/chapters/delete', [App\Http\Controllers\ChapterController::class, 'deleteChapter']);
    Route::post('/chapters/finish', [App\Http\Controllers\ChapterController::class, 'finishChapter']);
    Route::post('/chapters/update', [App\Http\Controllers\ChapterController::class, 'updateChapter']);
    Route::post('/chapters/create', [App\Http\Controllers\ChapterController::class, 'createChapter']);

    // library import
    Route::post('/import', [App\Http\Controllers\ImportController::class, 'import']);
    Route::post('/youtube/get-subtitle-list', [App\Http\Controllers\ImportController::class, 'getYoutubeSubtitles']);
    Route::post('/youtube/get-subtitle-file-content', [App\Http\Controllers\ImportController::class, 'getSubtitleFileContent']);
});