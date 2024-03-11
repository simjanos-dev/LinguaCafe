<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

// services
use App\Services\ReviewService;
use App\Services\GoalService;

// request classes
use App\Http\Requests\Review\GetReviewItemsRequest;
use App\Http\Requests\Review\UpdateReviewGoalRequest;

class ReviewController extends Controller {

    private $reviewService;
    private $goalService;

    public function __construct(ReviewService $reviewService, GoalService $goalService) {
        $this->middleware('auth');

        $this->reviewService = $reviewService;
        $this->goalService = $goalService;
    }
    
    public function getReviewItems(GetReviewItemsRequest $request) {
        $userId = Auth::user()->id;
        $language = Auth::user()->selected_language;
        $practiceMode = $request->post('practiceMode') === 'true';
        $chapterId = $request->post('chapterId');
        $bookId = $request->post('bookId');
        
        try {
            $reviews = $this->reviewService->getReviewItems($userId, $language, $bookId, $chapterId, $practiceMode);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        $reviewData = new \stdClass();
        $reviewData->reviews = $reviews;
        $reviewData->language = $language;

        return response()->json($reviewData, 200);
    }

    public function updateReadWordsGoal(UpdateReviewGoalRequest $request) {
        $userId = Auth::user()->id;
        $language = Auth::user()->selected_language;
        $readWords = $request->post('readWords');

        try {
            $this->goalService->updateGoalAchievement($userId, $language, 'read_words', $readWords);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json('Review goal has been updated successfully.', 200);
    }
}
