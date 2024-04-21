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

/*
    This function's authentication is inside the controller, because
    the first user can be created without being logged in.
*/
Route::group(['middleware' => 'web'], function () {
    Route::post('/users/create', [App\Http\Controllers\UserController::class, 'createUser']);
});

Route::group(['middleware' => ['auth', 'web']], function () {
    Route::get ('/users/get', [App\Http\Controllers\UserController::class, 'getUsers']);

    // users
    Route::get ('/users/get', [App\Http\Controllers\UserController::class, 'getUsers']);
    Route::post('/users/update', [App\Http\Controllers\UserController::class, 'updateUser']);
    Route::post('/users/update-password', [App\Http\Controllers\UserController::class, 'updatePassword']);
    Route::get ('/users/is-password-changed', [App\Http\Controllers\UserController::class, 'isUserPasswordChanged']);

    // languages
    Route::post('/languages/install', [App\Http\Controllers\LanguageController::class, 'installLanguage']);
    Route::get ('/languages/installed/list', [App\Http\Controllers\LanguageController::class, 'getInstalledLanguages']);
    Route::delete ('/languages/installed/delete', [App\Http\Controllers\LanguageController::class, 'deleteInstalledLanguages']);
    Route::get('/languages/get-language-selection-dialog-data', [App\Http\Controllers\LanguageController::class, 'getLanguageSelectionDialogData']);
    Route::get('/languages/get-admin-language-settings-data', [App\Http\Controllers\LanguageController::class, 'getAdminLanguageSettingsData']);
    Route::get('/languages/select/{language}', [App\Http\Controllers\LanguageController::class, 'selectLanguage']);

    // jellyfin
    Route::post('/jellyfin/request', [App\Http\Controllers\MediaPlayerController::class, 'jellyfinRequest']);
    Route::get('/jellyfin/subtitles', [App\Http\Controllers\MediaPlayerController::class, 'getJellyfinCurrentlyPlayedSubtitles']);
    Route::post('/jellyfin/process-subtitles', [App\Http\Controllers\MediaPlayerController::class, 'processJellyfinSubtitle']);

    // vue routes
    Route::get('/dev', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/user-settings', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/admin/{page?}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/user-manual/{currentPage?}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/attributions', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/patch-notes', [App\Http\Controllers\HomeController::class, 'index']);
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
    Route::get('/config/get/{configPath}', [App\Http\Controllers\HomeController::class, 'getConfig']);

    // user manual
    Route::get('/manual/get-menu-tree', [App\Http\Controllers\HomeController::class, 'getUserManualTree']);
    Route::get('/manual/get-manual-file/{fileName}', [App\Http\Controllers\HomeController::class, 'getUserManualFile']);

    // goals
    Route::post('/goals/get', [App\Http\Controllers\GoalController::class, 'getGoals']);
    Route::post('/goal/update', [App\Http\Controllers\GoalController::class, 'updateGoal']);
    Route::post('/goals/get-calendar-data', [App\Http\Controllers\GoalController::class, 'getCalendarData']);
    Route::post('/goals/achievement/update', [App\Http\Controllers\GoalController::class, 'updateCalendarData']);
    Route::get ('/goals/achievement/review/update', [App\Http\Controllers\GoalController::class, 'updateReviewGoalAchievement']);

    // settings
    Route::post('/settings/global/get', [App\Http\Controllers\SettingsController::class, 'getGlobalSettingsByName']);
    Route::post('/settings/global/update', [App\Http\Controllers\SettingsController::class, 'updateGlobalSettings']);
    Route::post('/settings/user/get', [App\Http\Controllers\SettingsController::class, 'getUserSettingsByName']);
    Route::post('/settings/user/update', [App\Http\Controllers\SettingsController::class, 'updateUserSettings']);
    
    // images 
    Route::get('/images/book_images/{fileName}', [App\Http\Controllers\ImageController::class, 'getBookImage']);

    // dictionaries
    Route::get('/dictionaries/scan', [App\Http\Controllers\DictionaryController::class, 'getImportableDictionaryList']);
    Route::post('/dictionaries/import', [App\Http\Controllers\DictionaryController::class, 'importSupportedDictionary']);
    Route::get('/dictionaries/get-record-count/{dictionaryName}', [App\Http\Controllers\DictionaryController::class, 'getDictionaryRecordCount']);
    Route::get('/dictionaries/deepl/get-usage', [App\Http\Controllers\DictionaryController::class, 'getDeeplCharacterLimit']);
    Route::post('/dictionaries/deepl/search', [App\Http\Controllers\DictionaryController::class, 'searchDeepl']);
    Route::get('/dictionaries/deepl/is-enabled', [App\Http\Controllers\DictionaryController::class, 'isDeeplEnabled']);
    Route::get('/dictionaries/get', [App\Http\Controllers\DictionaryController::class, 'getDictionaries']);
    Route::get('/dictionary/get/{dictionaryId}', [App\Http\Controllers\DictionaryController::class, 'getDictionary']);
    Route::post('/dictionary/update', [App\Http\Controllers\DictionaryController::class, 'updateDictionary']);
    Route::post('/dictionary/search', [App\Http\Controllers\DictionaryController::class, 'searchDefinitions']);
    Route::post('/dictionary/search-for-hover-vocabulary', [App\Http\Controllers\DictionaryController::class, 'searchDefinitionsForHoverVocabulary']);
    Route::post('/dictionary/search/inflections', [App\Http\Controllers\DictionaryController::class, 'searchInflections']);
    Route::post('/dictionary/test-csv-file', [App\Http\Controllers\DictionaryController::class, 'testDictionaryCsvFile']);
    Route::post('/dictionary/import-csv-file', [App\Http\Controllers\DictionaryController::class, 'importDictionaryCsvFile']);
    Route::get('/dictionary/delete/{dictionaryName}', [App\Http\Controllers\DictionaryController::class, 'deleteDictionary']);
    Route::get('/jmdict/xml-to-text', [App\Http\Controllers\DictionaryController::class, 'jmdictXmlToText']);

    // vocabulary
    Route::get ('/vocabulary/words/get/{wordId}', [App\Http\Controllers\VocabularyController::class, 'getUniqueWord']);
    Route::post('/vocabulary/word/update', [App\Http\Controllers\VocabularyController::class, 'updateWord']);
    Route::get ('/vocabulary/phrases/get/{phraseId}', [App\Http\Controllers\VocabularyController::class, 'getPhrase']);
    Route::post('/vocabulary/phrases/create', [App\Http\Controllers\VocabularyController::class, 'createPhrase']);
    Route::post('/vocabulary/phrases/update', [App\Http\Controllers\VocabularyController::class, 'updatePhrase']);
    Route::post('/vocabulary/phrases/delete', [App\Http\Controllers\VocabularyController::class, 'deletePhrase']);
    Route::post('/vocabulary/example-sentence/create-or-update', [App\Http\Controllers\VocabularyController::class, 'createOrUpdateExampleSentence']);
    Route::post('/vocabulary/search', [App\Http\Controllers\VocabularyController::class, 'searchVocabulary']);
    Route::post('/vocabulary/export-to-csv', [App\Http\Controllers\VocabularyController::class, 'exportToCsv']);
    Route::post('/vocabulary/import-from-csv', [App\Http\Controllers\VocabularyController::class, 'importFromCsv']);
    Route::get ('/vocabulary/example-sentence/{targetType}/{targetId}', [App\Http\Controllers\VocabularyController::class, 'getExampleSentence']);
    Route::post('/kanji/search', [App\Http\Controllers\VocabularyController::class, 'searchKanji']);
    Route::post('/kanji/details', [App\Http\Controllers\VocabularyController::class, 'getKanjiDetails']);
    
    // review
    Route::post('/reviews', [App\Http\Controllers\ReviewController::class, 'getReviewItems']);
    Route::post('/reviews/update', [App\Http\Controllers\ReviewController::class, 'updateReadWordsGoal']);

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
    Route::post('/subtitle/get-subtitle-file-content', [App\Http\Controllers\ImportController::class, 'getSubtitleFileContent']);
    Route::post('/website/get-text', [App\Http\Controllers\ImportController::class, 'getWebsiteText']);
});