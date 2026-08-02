<?php

namespace App\Services;

use App\DataTransferObjects\Calendar\CalendarData;
use App\Enums\GoalTypeEnum;
use App\Helpers\Language\LanguageConfig;
use App\Models\EncounteredWord;
use App\Models\Goal;
use App\Models\GoalAchievement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GoalService
{
    public function __construct() {}

    public function createGoalsForLanguage(User $user, LanguageConfig $language): void
    {
        $goal = Goal::query()
            ->where('user_id', $user->id)
            ->where('language', $language->name)
            ->first();

        if (!$goal) {
            $goal = new Goal();
            $goal->user_id = $user->id;
            $goal->language = $language->name;
            $goal->name = 'Reviews';
            $goal->type = 'review';
            $goal->quantity = 0;
            $goal->save();

            $goal = new Goal();
            $goal->user_id = $user->id;
            $goal->language = $language->name;
            $goal->name = 'Reading';
            $goal->type = 'read_words';
            $goal->quantity = 1000;
            $goal->save();

            $goal = new Goal();
            $goal->user_id = $user->id;
            $goal->language = $language->name;
            $goal->name = 'New words';
            $goal->type = 'learn_words';
            $goal->quantity = 10;
            $goal->save();
        }
    }

    public function updateOrCreateTodaysGoalAchievement(User $user, LanguageConfig $language, GoalTypeEnum $goalType, int $achievedQuantity): void
    {
        $goal = Goal::query()
            ->where('user_id', $user->id)
            ->where('language', $language->name)
            ->where('type', $goalType->value)
            ->firstOrFail();

        $achievement = GoalAchievement::query()
            ->where('user_id', $user->id)
            ->where('language', $language->name)
            ->where('goal_id', $goal->id)
            ->where('day', Carbon::now()->toDateString())
            ->first();

        if (!$achievement) {
            $achievement = new GoalAchievement();
            $achievement->language = $language->name;
            $achievement->user_id = $user->id;
            $achievement->goal_id = $goal->id;
            $achievement->achieved_quantity = 0;
            $achievement->goal_quantity = $goal->quantity;
            $achievement->day = Carbon::now()->toDateString();
        }

        $achievement->achieved_quantity += $achievedQuantity;
        $achievement->save();
    }

    public function getGoals(User $user, LanguageConfig $language): Collection
    {
        $goals = Goal::query()
            ->where('user_id', $user->id)
            ->where('language', $language->name)
            ->get();

        $goals->transform(function (Goal $goal) {
            $goal->todays_quantity = $goal->getTodaysQuantity();

            return $goal;
        });

        return $goals;
    }

    public function updateGoal(User $user, Goal $goal, int $newGoalQuantity): void
    {
        if ($goal->user_id !== $user->id) {
            throw new \Exception('Goal not found or unauthorized.');
        }

        $goal->quantity = $newGoalQuantity;
        $goal->save();

        // also update today's goal achievement
        $achievement = GoalAchievement::query()
            ->where('user_id', $user->id)
            ->where('goal_id', $goal->id)
            ->where('day', Carbon::today()->format('Y-m-d'))
            ->first();

        if ($achievement) {
            $achievement->goal_quantity = $newGoalQuantity;
            $achievement->save();
        }
    }

    public function getCalendarData(User $user, LanguageConfig $language): CalendarData
    {
        $goals = Goal::query()
            ->select([
                'id',
                'name',
                'type',
                'quantity',
            ])
            ->where('user_id', '=', $user->id)
            ->where('language', '=', $language->name)
            ->with('goalAchievements', function ($query) {
                $query->select([
                    'id',
                    'achieved_quantity',
                    'goal_quantity',
                    'day',
                    'goal_id',
                ]);
            })
            ->get()
            ->each(function ($goal) {
                $goal->setRelation('goalAchievements', $goal->goalAchievements->keyBy('day'));
            })
            ->keyBy('type');

        $reviews = EncounteredWord::query()
            ->where('user_id', '=', $user->id)
            ->where('language', '=', $language->name)
            ->whereNotNull('next_review')
            ->selectRaw(DB::raw('next_review as day, count(id) as quantity'))
            ->groupBy('next_review')
            ->get()
            ->keyBy('day');

        return new CalendarData(
            goals: $goals,
            reviews: $reviews,
        );
    }

    public function updateOrCreateGoalAchievement(
        User $user,
        LanguageConfig $language,
        ?GoalAchievement $goalAchievement,
        GoalTypeEnum $goalType,
        string $day,
        int $quantity
    ): void {
        if ($goalAchievement) {
            $goalAchievement->achieved_quantity = $quantity;
            $goalAchievement->save();
        } else {
            $goal = Goal::query()
                ->where('user_id', $user->id)
                ->where('language', $language->name)
                ->where('type', $goalType->value)
                ->firstOrFail();

            $achievement = new GoalAchievement();
            $achievement->user_id = $user->id;
            $achievement->language = $language->name;
            $achievement->goal_id = $goal->id;
            $achievement->achieved_quantity = $quantity;
            // TODO: goal_quantity should be null for review
            $achievement->goal_quantity = $goal->type == 'review' ? 1 : $goal->quantity;
            $achievement->day = $day;
            $achievement->save();
        }
    }
}
