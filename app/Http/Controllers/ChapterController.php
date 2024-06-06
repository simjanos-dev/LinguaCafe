<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

// request classes
use App\Http\Requests\Chapters\GetChaptersForBookRequest;
use App\Http\Requests\Chapters\GetChapterForEditorRequest;
use App\Http\Requests\Chapters\GetChapterForReaderRequest;
use App\Http\Requests\Chapters\FinishChapterRequest;
use App\Http\Requests\Chapters\UpdateChapterRequest;
use App\Http\Requests\Chapters\CreateChapterRequest;
use App\Http\Requests\Chapters\DeleteChapterRequest;

// services
use App\Services\ChapterService;

class ChapterController extends Controller {
    private $chapterService;

    public function __construct(ChapterService $chapterService) {
        $this->chapterService = $chapterService;
    }

    public function getChaptersForBook(GetChaptersForBookRequest $request) {
        $userId = Auth::user()->id;
        $bookId = intval($request->bookId);
        
        try {
            $chapters = $this->chapterService->getChaptersForBook($userId, $bookId);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($chapters, 200);
    }

    public function getChapterForEditor(GetChapterForEditorRequest $request) {
        $userId = Auth::user()->id;
        $chapterId = $request->chapterId;

        try {
            $chapter = $this->chapterService->getChapterForEditor($userId, $chapterId);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($chapter, 200);
    }

    public function getChapterForReader(GetChapterForReaderRequest $request) {        
        $userId = Auth::user()->id;
        $language = Auth::user()->selected_language;
        $chapterId = $request->chapterId;
        $languagesWithoutSpaces = config('linguacafe.languages.languages_without_spaces');
        
        try {
            $chapter = $this->chapterService->getChapterForReader($userId, $language, $languagesWithoutSpaces, $chapterId);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($chapter, 200);
    }

    public function finishChapter(FinishChapterRequest $request) {
        $userId = Auth::user()->id;
        $language = Auth::user()->selected_language;
        $uniqueWords = json_decode($request->uniqueWords);
        $leveledUpWords = json_decode($request->leveledUpWords);
        $leveledUpPhrases = json_decode($request->leveledUpPhrases);
        $autoMoveWordsToKnown = boolval($request->autoMoveWordsToKnown);
        $chapterId = $request->chapterId;

        try {
            $this->chapterService->finishChapter($userId, $chapterId, $autoMoveWordsToKnown, $uniqueWords, $leveledUpWords, $leveledUpPhrases, $language);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json('Tasks have been completed successfully.', 200);
    }

    public function createChapter(CreateChapterRequest $request) {
        $userId = Auth::user()->id;
        $chapterName = $request->chapterName;
        $bookId = $request->bookId;
        $chapterText = is_null($request->chapterText) ? '' : $request->chapterText;

        try {
            $this->chapterService->createChapter($userId, $bookId, $chapterName, $chapterText);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json('Chapter has been created successfully.', 200);
    }

    public function updateChapter(UpdateChapterRequest $request) {
        $userId = Auth::user()->id;
        $chapterName = $request->chapterName;
        $chapterId = $request->chapterId;
        $chapterText = $request->chapterText;

        try {
            $this->chapterService->updateChapter($userId, $chapterId, $chapterName, $chapterText);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json('Chapter has been updated successfully.', 200);
    }

    public function deleteChapter(DeleteChapterRequest $request) {
        $chapterId = $request->post('chapterId');
        $userId = Auth::user()->id;

        try {
            $this->chapterService->deleteChapter($userId, $chapterId);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json('Chapter has been deleted successfully.', 200);
    }
}