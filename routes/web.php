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
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index']);
    Route::get('/dev', [App\Http\Controllers\HomeController::class, 'dev']);
    Route::get('/jisho-request/{keyword}', [App\Http\Controllers\HomeController::class, 'jishoRequest']);
    Route::get('/language/{language}', [App\Http\Controllers\HomeController::class, 'changeLanguage']);
    Route::get('/dev', [App\Http\Controllers\HomeController::class, 'dev']);

    // tools
    Route::get('/jmdict-text-generator', [App\Http\Controllers\AdminController::class, 'jmdictTextGenerator']);

    // images 
    Route::get('/images/flags/{name}', [App\Http\Controllers\ImageController::class, 'getFlagImage']);
    Route::get('/images/course_covers/{name}', [App\Http\Controllers\ImageController::class, 'getCourseCoverImage']);

    // vocabulary
    Route::get('/kanji-print', [App\Http\Controllers\VocabularyController::class, 'kanjiPrint']);
    Route::get('/vocabulary-practice/{mode?}/{lessonId?}/{courseId?}', [App\Http\Controllers\VocabularyController::class, 'vocabularyPractice']);
    Route::post('/finish-vocabulary-practice', [App\Http\Controllers\VocabularyController::class, 'finishVocabularyPractice']);

    // flash cards
    Route::get('/flash-card-collections', [App\Http\Controllers\FlashCardController::class, 'flashCardCollections']);
    Route::get('/create-flash-card-collection', [App\Http\Controllers\FlashCardController::class, 'createFlashCardCollection']);
    Route::get('/edit-flash-card-collection/{flashCardCollectionId}', [App\Http\Controllers\FlashCardController::class, 'editFlashCardCollection']);
    Route::post('/save-flash-card-collection', [App\Http\Controllers\FlashCardController::class, 'saveFlashCardCollection']);
    Route::get('/delete-flash-card-collection/{flashCardCollectionId}', [App\Http\Controllers\FlashCardController::class, 'deleteFlashCardCollection']);
    Route::get('/flash-card-practice/{flashCardCollectionId}', [App\Http\Controllers\FlashCardController::class, 'practiceFlashCards']);
    Route::post('/finish-flash-card-practice', [App\Http\Controllers\FlashCardController::class, 'finishFlashCardPractice']);
    
    // lessons
    Route::get('/courses', [App\Http\Controllers\LessonController::class, 'courses']);
    Route::get('/create-course', [App\Http\Controllers\LessonController::class, 'getCreateCourse']);
    Route::post('/create-course', [App\Http\Controllers\LessonController::class, 'postCreateCourse']);
    Route::get('/lessons/{courseId}', [App\Http\Controllers\LessonController::class, 'lessons']);
    Route::get('/create-lesson/{courseId}', [App\Http\Controllers\LessonController::class, 'createLesson']);
    Route::get('/edit-lesson/{courseId}', [App\Http\Controllers\LessonController::class, 'editLesson']);
    Route::post('/save-lesson', [App\Http\Controllers\LessonController::class, 'saveLesson']);
    Route::get('/lesson/{lessonId}', [App\Http\Controllers\LessonController::class, 'lesson']);
    Route::post('/finish-lesson', [App\Http\Controllers\LessonController::class, 'finishLesson']);
    Route::get('/delete-lesson/{lessonId}', [App\Http\Controllers\LessonController::class, 'deleteLesson']);
});