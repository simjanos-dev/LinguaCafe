<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Book;

// request classes
use App\Models\Chapter;
use App\Services\ChapterService;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Language\LanguageConfig;
use App\Http\Resources\Chapter\ChapterResource;
use App\Http\Requests\Chapters\CreateChapterRequest;
use App\Http\Requests\Chapters\DeleteChapterRequest;
use App\Http\Requests\Chapters\FinishChapterRequest;
use App\Http\Requests\Chapters\UpdateChapterRequest;
use App\Http\Resources\Chapter\ChapterResourceCollection;
use App\Http\Requests\Chapters\GetChapterForEditorRequest;
use App\Http\Requests\Chapters\GetChapterForReaderRequest;
use App\Http\Requests\Chapters\RetryFailedChaptersRequest;

class ChapterController extends Controller {

    public function __construct(private ChapterService $chapterService)
    {
        //
    }

    public function getChaptersForBook(Book $book) 
    {
        $user = Auth::user();
        
        $chapters = $this->chapterService->getChaptersForBook($user, $book);

        return new ChapterResourceCollection($chapters);
    }

    public function getChaptersWordsCount(Book $book)
    {
        $user = Auth::user();
        
        $chapters = $this->chapterService->getChaptersWordsCount($user, $book);

        return response()->json($chapters, 200);
    }

    public function getChapter(Chapter $chapter) {
        $user = Auth::user();
        
        $transformedChapter = $this->chapterService->getChapterForEditor($user, $chapter);

        return new ChapterResource($transformedChapter);
    }

    public function getChapterForReader(Chapter $chapter) {        
        $user = Auth::user();
        $language = LanguageConfig::load($user->selected_language);
        
        $chapter = $this->chapterService->getChapterForReader($user, $language, $chapter);

        return response()->json($chapter, 200);
    }

    public function finishChapter(FinishChapterRequest $request, Chapter $chapter) {
        $user = Auth::user();
        $uniqueWords = json_decode($request->validated('uniqueWords'));
        $autoLevelUpWords = (bool) $request->validated('autoLevelUpWords');
        $leveledUpWords = json_decode($request->validated('leveledUpWords'));
        $leveledUpPhrases = json_decode($request->validated('leveledUpPhrases'));
        $autoMoveWordsToKnown = (bool) $request->validated('autoMoveWordsToKnown');

        $this->chapterService->finishChapter(
            user: $user, 
            chapter: $chapter, 
            autoMoveWordsToKnown: $autoMoveWordsToKnown, 
            uniqueWords: $uniqueWords, 
            autoLevelUpWords: $autoLevelUpWords, 
            leveledUpWords: $leveledUpWords, 
            leveledUpPhrases: $leveledUpPhrases
        );

        return response()->noContent();
    }

    public function createChapter(CreateChapterRequest $request, Book $book) {
        $user = Auth::user();
        $text = $request->validated('text');
        $name = $request->validated('name');

        $this->chapterService->createChapter(
            $user, 
            $book, 
            $name, 
            $text ?? ''
        );

        return response()->json('Chapter has been created successfully.', 200);
    }

    public function updateChapter(UpdateChapterRequest $request, Chapter $chapter) {
        $user = Auth::user();
        $text = $request->validated('text');
        $name = $request->validated('name');

        $this->chapterService->updateChapter(
            $user, 
            $chapter, 
            $name, 
            $text ?? ''
        );

        return response()->noContent();
    }

    public function deleteChapter(Chapter $chapter) {
        $user = Auth::user();

        $this->chapterService->deleteChapter($user, $chapter);

        return response()->noContent();
    }

    public function retryFailedChapters(Book $book) {
        $user = Auth::user();

        $this->chapterService->retryFailedChapters($user, $book);

        return response()->noContent();
    }
}