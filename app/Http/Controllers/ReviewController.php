<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Goal;
use App\Models\GoalAchievement;
use App\Services\ReviewService;

// request classes
use App\Http\Requests\Review\GetReviewItemsRequest;

class ReviewController extends Controller {

    private $reviewService;

    public function __construct(ReviewService $reviewService) {
        $this->middleware('auth');

        $this->reviewService = $reviewService;
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

    public function updateReviewCounts(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        
        // updage today's reading achievement
        $goal = Goal::where('user_id', Auth::user()->id)
            ->where('language', $selectedLanguage)
            ->where('type', 'read_words')
            ->first();
        
        $achievement = GoalAchievement::where('user_id', Auth::user()->id)
        ->where('language', $selectedLanguage)
        ->where('goal_id', $goal->id)
        ->where('day', Carbon::now()->toDateString())
        ->first();

        if (!$achievement) {
            $achievement = new GoalAchievement();
            $achievement->language = $selectedLanguage;
            $achievement->user_id = Auth::user()->id;
            $achievement->goal_id = $goal->id;
            $achievement->achieved_quantity = 0;
            $achievement->goal_quantity = $goal->quantity;
            $achievement->day = Carbon::now()->toDateString();
        }
        

        $achievement->achieved_quantity += $request->readWords;
        $achievement->save();

        //$request->readWords

        return 'success';
    }
}
