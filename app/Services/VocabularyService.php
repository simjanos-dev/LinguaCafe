<?php

namespace App\Services;

use App\Models\EncounteredWord;

class VocabularyService
{
    public function __construct()
    {
    }

    public function getUniqueWord($userId, $wordId) {
        $word = EncounteredWord
            ::where('user_id', $userId)
            ->where('id', $wordId)
            ->first();
        
        if (!$word) {
            throw new \Exception('Word does not exist, or it belongs to a different user.');
        }

        return $word;
    }
}