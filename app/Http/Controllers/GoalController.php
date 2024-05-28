<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Goal;
use App\Models\GoalAchievement;
use App\Models\EncounteredWord;

use App\Services\GoalService;

// request classes
use App\Http\Requests\Goals\UpdateGoalRequest;
use App\Http\Requests\Goals\UpdateCalendarDataRequest;

class GoalController extends Controller
{
    private $goalService;
    
    public function __construct(GoalService $goalService) {
        $this->goalService = $goalService;
    }

    public function getGoals() {
        $userId = Auth::user()->id;
        $language = Auth::user()->selected_language;

        try {
            $goals = $this->goalService->getGoals($userId, $language);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($goals, 200);
    }

    public function updateGoal(UpdateGoalRequest $request) {
        $userId = Auth::user()->id;
        $goalId = $request->post('goalId');
        $newGoalQuantity = $request->post('newGoalQuantity');

        try {
            $this->goalService->updateGoal($userId, $goalId, $newGoalQuantity);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json('Goal has been updated successfully.', 200);
    }

    public function getCalendarData() {
        $userId = Auth::user()->id;
        $language = Auth::user()->selected_language;
        
        try {
            $calendarData = $this->goalService->getCalendarData($userId, $language);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return $calendarData;
    }

    /* 
        Updates a GoalAchievement, or creates one if it 
        doesn't exists yet for the given day and type.
    */
    public function updateCalendarData(UpdateCalendarDataRequest $request) {
        $userId = Auth::user()->id;
        $language = Auth::user()->selected_language;
        $achievementGoalId = $request->post('achievementGoalId');
        $achievementType = $request->post('achievementType');
        $day = $request->post('day');
        $newValue = $request->post('newValue');
        
        try {
            $this->goalService->updateCalendarData($userId, $language, $achievementGoalId, $achievementType, $day, $newValue);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json('Calendar data has been updated successfully.', 200);
    }

    public function updateReviewGoalAchievement() {
        $userId = Auth::user()->id;
        $language = Auth::user()->selected_language;

        try {
            $this->goalService->updateGoalAchievement($userId, $language, 'review', 1);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json('Goals have been updated successfully.', 200);
    }
}
