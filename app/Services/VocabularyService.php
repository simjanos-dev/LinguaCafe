<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\Models\EncounteredWord;
use App\Models\Phrase;
use App\Models\Lesson;
use App\Models\ExampleSentence;
use App\Models\TextBlock;

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

    public function updateWord($userId, $wordId, $wordData, $wordStage = null) {
        $word = EncounteredWord
            ::where('user_id', $userId)
            ->where('id', $wordId)
            ->first();
        
        if (!$word) {
            throw new \Exception('Word does not exist, or it belongs to a different user.');
        }
        
        if ($wordStage !== null) {
            $word->setStage($wordStage);
        }

        $word->update($wordData);
        $word->save();

        return true;
    }

    public function createPhrase($userId, $language, $words, $stage, $reading, $translation) {
        $phrase = new Phrase();
        $phrase->user_id = $userId;
        $phrase->language = $language;
        $phrase->stage = $stage;
        $phrase->reading = $reading;
        $phrase->translation = $translation;
        $phrase->words = json_encode($words);
        $phrase->words_searchable = implode('', $words);
        $phrase->save();

        // update phrase ids in lesson texts
        $phraseWords = array_unique($words);
        $lessons = Lesson
            ::where('user_id', $userId)
            ->where('language', $language)
            ->get();

        foreach ($lessons as $lesson) {
            $uniqueWords = json_decode($lesson->unique_words);
            if (count(array_intersect($uniqueWords, $phraseWords)) !== count($phraseWords)) {
                continue;
            }

            $words = $lesson->getProcessedText();

            $textBlock = new TextBlock();
            $textBlock->setProcessedWords($words);
            $textBlock->collectUniqueWords();
            $phraseIdsChanged = $textBlock->updatePhraseIds($phrase);

            // save lesson words
            if ($phraseIdsChanged) {
                $lesson->setProcessedText($textBlock->processedWords);
                $lesson->save();
            }
        }

        // update phrase ids in example sentences
        $exampleSentences = ExampleSentence
            ::where('user_id', $userId)
            ->where('language', $language)
            ->get();

        DB::beginTransaction();
        foreach ($exampleSentences as $exampleSentence) {
            $uniqueWords = json_decode($exampleSentence->unique_words);
            if (count(array_intersect($uniqueWords, $phraseWords)) !== count($phraseWords)) {
                continue;
            }

            $textBlock = new TextBlock();
            $textBlock->setProcessedWords(json_decode($exampleSentence->words));
            $textBlock->collectUniqueWords();
            $textBlock->updatePhraseIds($phrase);
            $textBlock->createNewEncounteredWords();
            
            $exampleSentence->words = json_encode($textBlock->processedWords);
            $exampleSentence->unique_words = json_encode($textBlock->uniqueWords);
            $exampleSentence->save();
        }

        DB::commit();

        return $phrase->id;
    }

    public function updatePhrase($userId, $phraseId, $phraseData, $phraseStage = null) {
        $phrase = Phrase
            ::where('user_id', $userId)
            ->where('id', $phraseId)
            ->first();
        
        if (!$phrase) {
            throw new \Exception('Phrase does not exist, or it belongs to a different user.');
        }
        
        if ($phraseStage !== null) {
            $phrase->setStage($phraseStage);
        }

        $phrase->update($phraseData);
        $phrase->save();

        return true;
    }

    public function getPhrase($userId, $phraseId) {
        $phrase = Phrase
            ::where('user_id', $userId)
            ->where('id', $phraseId)
            ->first();

        if (!$phrase) {
            throw new \Exception('Phrase does not exist, or it belongs to a different user.');
        }

        return $phrase;
    }

    public function deletePhrase($userId, $language, $phraseId) {

        $phrase = Phrase
            ::where('user_id', $userId)
            ->where('language', $language)
            ->where('id', $phraseId)
            ->first();

        if (!$phrase) {
            throw new \Exception('Phrase does not exist, or it belongs to a different user.');
        }

        // remove phrase ids from text words
        $chapters = Lesson
            ::where('user_id', $userId)
            ->where('language', $language)
            ->get();

        foreach($chapters as $chapter) {
            $words = $chapter->getProcessedText();
            $chapterChanged = false;

            // delete phrase id from lesson words
            foreach ($words as $word) {
                $index = array_search($phraseId, $word->phrase_ids);
                if ($index !== false) {
                    $modifiedPhraseIds = $word->phrase_ids;
                    array_splice($modifiedPhraseIds, $index, 1);
                    $word->phrase_ids = $modifiedPhraseIds;
                    $chapterChanged = true;
                }
            }

            // save lesson if changed
            if ($chapterChanged) {
                $chapter->setProcessedText($words);
                $chapter->save();
            }
        }

        // remove phrase ids from example sentence words
        $exampleSentences = ExampleSentence
            ::where('user_id', $userId)
            ->where('language', $language)
            ->get();

        DB::beginTransaction();
        foreach ($exampleSentences as $exampleSentence) {
            $exampleSentence->deletePhraseId($phraseId);
        }

        DB::commit();
        
        ExampleSentence
            ::where('user_id', $userId)
            ->where('target_type', 'phrase')
            ->where('target_id', $phraseId)
            ->delete();

        Phrase
            ::where('user_id', $userId)
            ->where('language', $language)
            ->where('id', $phraseId)
            ->delete();

        return true;
    }
}