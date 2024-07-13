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

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__.'/auth.php';

/*
    This function's authentication is inside the controller, because
    the first user can be created without being logged in.
*/
Route::group(['middleware' => 'web'], function () {
    Route::post('/users/create', [App\Http\Controllers\UserController::class, 'createUser']);
});

// login routes
Route::get('/login', [App\Http\Controllers\UserController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\UserController::class, 'authenticateUser']);

Route::group(['middleware' => ['auth', 'web']], function () {

    Route::group(['middleware' => 'admin'], function () {
        Route::get('/dev', [App\Http\Controllers\HomeController::class, 'index']);
        
        // users
        Route::get ('/users/get', [App\Http\Controllers\UserController::class, 'getUsers']);
        Route::post('/users/update', [App\Http\Controllers\UserController::class, 'updateUser']);

        // languages
        Route::post('/languages/install', [App\Http\Controllers\LanguageController::class, 'installLanguage']);
        Route::get ('/languages/installed/list', [App\Http\Controllers\LanguageController::class, 'getInstalledLanguages']);
        Route::delete ('/languages/installed/delete', [App\Http\Controllers\LanguageController::class, 'deleteInstalledLanguages']);
        Route::get('/languages/get-admin-language-settings-data', [App\Http\Controllers\LanguageController::class, 'getAdminLanguageSettingsData']);
        
        // dictionaries
        Route::post('/dictionary/update', [App\Http\Controllers\DictionaryController::class, 'updateDictionary']);
        
        // vue routes            
        Route::get('/admin/{page?}', [App\Http\Controllers\HomeController::class, 'index']);

        // fonts
        Route::get ('/fonts/get', [App\Http\Controllers\FontTypeController::class, 'getInstalledFontTypes']);
        Route::post('/fonts/upload', [App\Http\Controllers\FontTypeController::class, 'uploadFontType']);
        Route::post('/fonts/update', [App\Http\Controllers\FontTypeController::class, 'updateFontType']);
        Route::post('/fonts/delete', [App\Http\Controllers\FontTypeController::class, 'deleteFontType']);

        // settings
        Route::post('/settings/global/update', [App\Http\Controllers\SettingsController::class, 'updateGlobalSettings']);
        Route::post('/settings/global/get', [App\Http\Controllers\SettingsController::class, 'getGlobalSettingsByName']);

        // dictionaries
        Route::post('/dictionaries/get-supported-dictionary-file-information', [App\Http\Controllers\DictionaryController::class, 'getDictionaryFileInformation']);
        Route::post('/dictionaries/import', [App\Http\Controllers\DictionaryController::class, 'importSupportedDictionary']);
        Route::get('/dictionaries/get-record-count/{dictionaryTableName}', [App\Http\Controllers\DictionaryController::class, 'getDictionaryRecordCount']);
        Route::get('/dictionaries/deepl/get-usage', [App\Http\Controllers\DictionaryController::class, 'getDeeplCharacterLimit']);
        Route::get('/dictionaries/get', [App\Http\Controllers\DictionaryController::class, 'getDictionaries']);
        Route::get('/dictionaries/get/{dictionaryId}', [App\Http\Controllers\DictionaryController::class, 'getDictionary']);
        Route::post('/dictionaries/update', [App\Http\Controllers\DictionaryController::class, 'updateDictionary']);
        Route::post('/dictionaries/test-csv-file', [App\Http\Controllers\DictionaryController::class, 'testDictionaryCsvFile']);
        Route::post('/dictionaries/import-csv-file', [App\Http\Controllers\DictionaryController::class, 'importDictionaryCsvFile']);
        Route::post('/dictionaries/create-deepl', [App\Http\Controllers\DictionaryController::class, 'createDeeplDictionary']);
        Route::get('/dictionaries/delete/{dictionaryId}', [App\Http\Controllers\DictionaryController::class, 'deleteDictionary']);
        Route::get('/jmdict/xml-to-text', [App\Http\Controllers\DictionaryController::class, 'jmdictXmlToText']);
    });

    // languages
    Route::get('/languages/get-language-selection-dialog-data', [App\Http\Controllers\LanguageController::class, 'getLanguageSelectionDialogData']);
    Route::get('/languages/select/{language}', [App\Http\Controllers\LanguageController::class, 'selectLanguage']);

    // users
    Route::post('/users/update-password', [App\Http\Controllers\UserController::class, 'updatePassword']);
    Route::get ('/users/is-password-changed', [App\Http\Controllers\UserController::class, 'isUserPasswordChanged']);

    // jellyfin
    Route::get('/jellyfin/subtitles', [App\Http\Controllers\JellyfinController::class, 'getJellyfinCurrentlyPlayedSubtitles']);

    // vue routes    
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/user-settings', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/user-manual/{currentPage?}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/attributions', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/patch-notes', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/books/{bookId?}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/book/create', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/chapters/{id}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/chapters/read/{id}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/chapters/create/{bookId}', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/chapters/edit/{bookId}/{chapterId}', [App\Http\Controllers\HomeController::class, 'index']);
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
    Route::post('/goals/achievement/review/update', [App\Http\Controllers\GoalController::class, 'updateReviewGoalAchievement']);

    // fonts
    Route::get('/fonts/get-fonts-for-language/{language}', [App\Http\Controllers\FontTypeController::class, 'getFontTypesForLanguage']);
    Route::get('/fonts/file/{fileName}', [App\Http\Controllers\FontTypeController::class, 'getFontTypeFile']);

    // settings
    Route::post('/settings/user/get', [App\Http\Controllers\SettingsController::class, 'getUserSettingsByName']);
    Route::post('/settings/user/update', [App\Http\Controllers\SettingsController::class, 'updateUserSettings']);
    Route::get('/settings/is-jellyfin-enabled', [App\Http\Controllers\SettingsController::class, 'isJellyfinEnabled']);
    Route::get('/settings/get-anki-settings', [App\Http\Controllers\SettingsController::class, 'getAnkiSettings']);

    // images
    Route::get('/images/book_images/{fileName}', [App\Http\Controllers\ImageController::class, 'getBookImage']);
    Route::get('/images/kanji/{fileName}', [App\Http\Controllers\ImageController::class, 'getKanjiImage']);

    // dictionaries
    Route::post('/dictionaries/deepl/search', [App\Http\Controllers\DictionaryController::class, 'searchDeepl']);
    Route::get('/dictionaries/deepl/is-enabled', [App\Http\Controllers\DictionaryController::class, 'isDeeplEnabled']);
    Route::post('/dictionaries/search', [App\Http\Controllers\DictionaryController::class, 'searchDefinitions']);
    Route::post('/dictionaries/search-for-hover-vocabulary', [App\Http\Controllers\DictionaryController::class, 'searchDefinitionsForHoverVocabulary']);
    Route::post('/dictionaries/search/inflections', [App\Http\Controllers\DictionaryController::class, 'searchInflections']);

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
    Route::get ('/chapters/word-counts/{bookId}', [App\Http\Controllers\ChapterController::class, 'getChaptersBookCount']);
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
