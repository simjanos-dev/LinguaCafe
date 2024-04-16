<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\GoalAchievement;
use App\Models\EncounteredWord;

class StatisticsService {
    
    public function __construct() {
    }

    public function getStatistics($userId, $language) {
        $languageStatistics = new \stdClass();

        $readingGoal = Goal::where('user_id', $userId)
            ->where('language', $language)
            ->where('type', 'read_words')
            ->first();

        $languageStatistics->days = new \stdClass();
        $languageStatistics->days->name = 'Days of activity';
        $languageStatistics->days->icon = 'mdi-calendar-check';
        $languageStatistics->days->value = GoalAchievement
            ::where('user_id', $userId)
            ->where('language', $language)
            ->where('achieved_quantity', '<>', 0)
            ->distinct('day')
            ->count('day');

        $languageStatistics->readWordCount = new \stdClass();
        $languageStatistics->readWordCount->name = 'Read words';
        $languageStatistics->readWordCount->icon = 'mdi-book-open-variant';
        $languageStatistics->readWordCount->value = GoalAchievement
            ::where('user_id', $userId)
            ->where('language', $language)
            ->where('goal_id', $readingGoal->id)
            ->sum('achieved_quantity');
        
        if ($language == 'japanese') {
            // get unique kanji
            $uniqueKanji = [];
            $words = EncounteredWord
                ::where('stage', '<=', 0)
                ->where('language', 'japanese')
                ->where('user_id', $userId)
                ->get();

            foreach ($words as $word) {
                $kanji = preg_split("//u", $word->kanji, -1, PREG_SPLIT_NO_EMPTY);
                foreach($kanji as $currentKanji) {
                    if(!in_array($currentKanji, $uniqueKanji, true)) {
                        array_push($uniqueKanji, $currentKanji);
                    }
                }
            }
            
            $languageStatistics->kanji = new \stdClass();
            $languageStatistics->kanji->name = 'Kanji';
            $languageStatistics->kanji->value = count($uniqueKanji);
            $languageStatistics->kanji->icon = 'mdi-ideogram-cjk';
        }
        
        $languageStatistics->known = new \stdClass();
        $languageStatistics->known->name = 'Known words';
        $languageStatistics->known->icon = 'mdi-credit-card-check';
        $languageStatistics->known->value = EncounteredWord
            ::select('id')->where('stage', 0)
            ->where('user_id', $userId)
            ->where('language', $language)
            ->count('id');

        $languageStatistics->learning = new \stdClass();
        $languageStatistics->learning->name = 'Words currently studied';
        $languageStatistics->learning->icon = 'mdi-school';
        $languageStatistics->learning->value = EncounteredWord
            ::select('id')
            ->where('stage', '<', 0)
            ->where('user_id', $userId)
            ->where('language', $language)
            ->count('id');
        
        return $languageStatistics;
    }
}