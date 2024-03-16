<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use League\Csv\Writer;
use League\Csv\Reader;
use League\Csv\Statement;


use App\Models\EncounteredWord;
use App\Models\Phrase;
use App\Models\Book;
use App\Models\Lesson;
use App\Models\ExampleSentence;
use App\Models\TextBlock;
use App\Models\Kanji;
use App\Models\Radical;

class VocabularyService {
    private $itemsPerPage;

    public function __construct() {
        $this->itemsPerPage = 30;
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

        if ($language === 'japanese' || $language === 'chinese') {
            $phrase->words_searchable = implode('', $words);
        } else {
            $phrase->words_searchable = implode(' ', $words);
        }
        
        $phrase->save();

        // update phrase ids in lesson texts
        $phraseWords = array_unique($words);
        $chapters = Lesson
            ::where('user_id', $userId)
            ->where('language', $language)
            ->get();

        foreach ($chapters as $chapter) {
            $uniqueWords = json_decode($chapter->unique_words);
            if (count(array_intersect($uniqueWords, $phraseWords)) !== count($phraseWords)) {
                continue;
            }

            $words = $chapter->getProcessedText();

            $textBlock = new TextBlock();
            $textBlock->setProcessedWords($words);
            $textBlock->collectUniqueWords();
            $phraseIdsChanged = $textBlock->updatePhraseIds($phrase);

            // save lesson words
            if ($phraseIdsChanged) {
                $chapter->setProcessedText($textBlock->processedWords);
                $chapter->save();
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

    public function getExampleSentence($userId, $targetType, $targetId) {
        $exampleSentence = ExampleSentence
            ::where('user_id', $userId)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->first();
        
        if (!$exampleSentence) {
            throw new \Exception('Example sentence does not exist, or it belongs to a different user.');
        }
        
        $textBlock = new TextBlock();
        $textBlock->setProcessedWords(json_decode($exampleSentence->words));
        $textBlock->uniqueWords = json_decode($exampleSentence->unique_words);
        $textBlock->prepareTextForReader();
        $textBlock->indexPhrases();

        return $textBlock->getReaderData();
    }

    public function createOrUpdateExampleSentence($userId, $language, $targetType, $targetId, $exampleSentenceWords) {
        // Retrieve example sentence.
        $exampleSentence = ExampleSentence
            ::where('user_id', $userId)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->first();

        // Create new example sentence record if it didn't exist, and update words.
        if (!$exampleSentence) {
            $exampleSentence = new ExampleSentence();
            $exampleSentence->user_id = $userId;
            $exampleSentence->language = $language;
            $exampleSentence->target_type = $targetType;
            $exampleSentence->target_id = $targetId;
            $exampleSentence->unique_words = [];
        }

        // Update unique words.
        $uniqueWords = [];
        foreach ($exampleSentenceWords as $word) {
            $lowerCaseWord = mb_strtolower($word->word, 'UTF-8');
            if (!in_array($lowerCaseWord, $uniqueWords, true)) {
                array_push($uniqueWords, $lowerCaseWord);
            }
        }
        
        $textBlock = new TextBlock();
        $textBlock->setProcessedWords($exampleSentenceWords);
        $textBlock->collectUniqueWords();
        $textBlock->updateAllPhraseIds();

        // Save example sentence.
        $exampleSentence->words = json_encode($textBlock->processedWords);
        $exampleSentence->unique_words = json_encode($textBlock->uniqueWords);
        $exampleSentence->save();

        return true;
    }

    public function searchVocabulary($userId, $language, $text, $bookId, $chapterId, $stage, $phrases, $orderBy, $translation, $page) {
        // get books and chapters
        $books = Book::where('user_id', $userId)->where('language', $language)->get();
        $bookIndex = -1;
        for ($i = 0; $i < count($books); $i++) {
            $books[$i]->chapters = Lesson::select(['id', 'name'])->where('user_id', $userId)->where('language', $language)->where('book_id', $books[$i]->id)->get();
            
            if (isset($bookId) && $books[$i]->id == $bookId) {
                $bookIndex = $i;
            }
        }

        $search = $this->buildSearchRequest($userId, $language, $text, $bookId, $chapterId, $stage, $phrases, $orderBy, $translation);

        $data = new \stdClass();
        $data->wordCount = $search->count();
        $data->words = $search->skip(($page - 1) * $this->itemsPerPage)->take($this->itemsPerPage)->get();
        $data->books = $books;
        $data->bookIndex = $bookIndex;
        $data->pageCount = ceil($data->wordCount / $this->itemsPerPage);
        $data->currentPage = $page;

        return $data;
    }

    public function exportToCsv($userId, $language, $text, $bookId, $chapterId, $stage, $phrases, $orderBy, $translation, $fields) {    
        $words = $this->buildSearchRequest($userId, $language, $text, $bookId, $chapterId, $stage, $phrases, $orderBy, $translation)->get();

        // create csv file
        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->setDelimiter('|');

        // insert headers to csv
        $csvArray = [];
        foreach ($fields as $field) {
            if ($field['export']) {
                $csvArray[] = str_replace('Stage', 'Level', $field['headerName']);
            }
        }
        
        $csv->insertOne($csvArray);

        // insert data to csv
        $phraseWordDelimiter = ($language === 'japanese' || $language === 'chinese') ? '' : ' ';
        foreach($words as $word) {
            $csvArray = [];
            foreach ($fields as $field) {
                if (!$field['export']) {
                    continue;
                }
                
                $searchObjectProperty = $field['searchObjectProperty'];

                if ($word->type === 'phrase' && $searchObjectProperty === 'word') {
                    $csvArray[] = implode($phraseWordDelimiter, json_decode($word->$searchObjectProperty));
                } else {
                    $csvArray[] = $word->$searchObjectProperty;
                }
            }
            
            $csv->insertOne($csvArray);
        }

        return $csv;
    }

    public function importFromCsv($userId, $language, $fileName, $delimiter, $onlyUpdate, $skipHeader) {
        $stageMapping = [
            'new' => 2,
            'ignored' => 1,
            'learned' => 0,
            '1' => -1,
            '2' => -2,
            '3' => -3,
            '4' => -4,
            '5' => -5,
            '6' => -6,
            '7' => -7,
        ];

        DB::disableQueryLog();
        $reader = Reader::createFromPath(storage_path('app/temp') . '/' . $fileName, 'r');
        $reader->setDelimiter($delimiter);
        $records = $reader->getRecords();
        $createdWords = 0;
        $updatedWords = 0;
        $rejectedWords = 0;

        // collect data from csv file
        DB::beginTransaction();
        foreach($records as $index => $record) {
            $lowerCaseWord = mb_strtolower($record[0]); 
            
            // skip header if option is enabled
            if ($index === 0 && $skipHeader) {
                continue;
            }

            // reject word if contains space character
            if (str_contains($lowerCaseWord, ' ')) {
                $rejectedWords ++;
                continue;
            }

            // reject word if it's too long
            if (mb_strlen($lowerCaseWord) >= 255) {
                $rejectedWords ++;
                continue;
            }

            // reject word if word field is missing
            if (mb_strlen($lowerCaseWord) === 0) {
                $rejectedWords ++;
                continue;
            }

            // reject word if it's stage is stage is an incorrect value
            $stage = isset($record[5]) ? $record[5] : 'learned';
            if (isset($record[5]) && !isset($stageMapping[$stage])) {
                $rejectedWords ++;
                continue;
            }

            // try to retrieve word
            $encounteredWord = EncounteredWord
                ::where('user_id', $userId)
                ->where('language', $language)
                ->where('word', $lowerCaseWord)
                ->first();

            // if does not exist, create it
            if (!$encounteredWord) {

                // reject word if does not exist and only update option is used
                if ($onlyUpdate) {
                    $rejectedWords ++;
                    continue;
                }

                $encounteredWord = new EncounteredWord();
                $encounteredWord->user_id = $userId;
                $encounteredWord->language = $language;
                $encounteredWord->word = $lowerCaseWord;
                $encounteredWord->translation = '';
                $encounteredWord->lemma = '';
                $encounteredWord->base_word = '';
                $encounteredWord->reading = '';
                $encounteredWord->base_word_reading = '';
                $encounteredWord->stage = 0;
                $encounteredWord->kanji = '';
                $encounteredWord->example_sentence = '';

                $createdWords ++;
            } else {
                $updatedWords ++;
            }

            // set translation
            if (isset($record[1])) {
                $encounteredWord->translation = $record[1];
            }
            
            // set lemma
            if (isset($record[2])) {
                $encounteredWord->base_word = $record[2];
            }
            
            // set reading
            if (isset($record[3])) {
                $encounteredWord->reading = $record[3];
            }
            
            // set lemma reading
            if (isset($record[4])) {
                $encounteredWord->base_word_reading = $record[4];
            }

            // set stage
            if (isset($record[5])) {
                $encounteredWord->stage = $stageMapping[$stage];
            }

            // save word with new data
            $encounteredWord->save();

            // add word to accepted words list
            $acceptedWords[] = $lowerCaseWord;
        }

        DB::commit();

        $responseData = new \StdClass();
        $responseData->createdWords = $createdWords;
        $responseData->updatedWords = $updatedWords;
        $responseData->rejectedWords = $rejectedWords;
        
        return $responseData;
    }

    /*
        Builds a search request. It's used for both searching and exporting vocabulary.
    */
    private function buildSearchRequest($userId, $language, $text, $bookId, $chapterId, $stage, $phrases, $orderBy, $translation) {
        $wordsToSkip = config('linguacafe.words_to_skip');

        // get words and phrases
        // from filtered chapters
        $filteredChapters = Lesson::where('user_id', $userId)->where('language', $language);
        $filteredWords = [];
        $filteredPhraseIds = [];
        if ($bookId !== -1) {
            $filteredChapters = $filteredChapters->where('book_id', $bookId);
        }

        if ($chapterId !== -1) {
            $filteredChapters = $filteredChapters->where('id', $chapterId);
        }
        
        $filteredChapters = $filteredChapters->get();

        if ($bookId !== -1) {
            foreach ($filteredChapters as $filteredChapter) {
                $chapter = Lesson
                    ::where('user_id', $userId)
                    ->where('id', $filteredChapter->id)
                    ->first();

                // add filtered phrase ids
                $filteredChapterWords = $chapter->getProcessedText();

                foreach ($filteredChapterWords as $filteredChapterWord) {
                    $filteredChapterWord->phrase_ids = $filteredChapterWord->phrase_ids;
                    foreach ($filteredChapterWord->phrase_ids as $phraseId) {
                        if (!in_array($phraseId, $filteredPhraseIds, true)) {
                            array_push($filteredPhraseIds, $phraseId);
                        }
                    }
                }

                // add filtered words
                $filteredChapterUniqueWords = json_decode($filteredChapter->unique_words);
                foreach ($filteredChapterUniqueWords as $filteredChapterUniqueWord) {
                    if (!in_array($filteredChapterUniqueWord, $filteredWords, true)) {
                        array_push($filteredWords, $filteredChapterUniqueWord);
                    }
                }
            }
        }

        // search for words and apply filters
        $wordSearch = EncounteredWord
            ::select('id', 'base_word', 'word', DB::raw("'' AS words_searchable"), 'reading', 'base_word_reading', 'stage', 'translation', 'read_count', 'lookup_count', 'added_to_srs', DB::raw("'word' AS type"))->where('user_id', $userId)
            ->where('language', $language)
            ->whereNotIn('word', $wordsToSkip);

        if ($text !== 'anytext') {
            $wordSearch = $wordSearch->where(function($query) use ($text) {
                $query->orWhere('word', 'like', '%' . $text . '%')
                    ->orWhere('reading', 'like', '%' . $text . '%');
            });
        }

        if ($bookId !== -1) {
            $wordSearch->whereIn('word', $filteredWords);
        }

        if ($stage !== -999) {
            $wordSearch = $wordSearch->where('stage', $stage);
        }

        if ($translation == 'not empty') {
            $wordSearch = $wordSearch->where('translation', '<>', '');
        }
        
        // search for phrases and apply filters
        $phraseSearch = Phrase
            ::select('id', DB::raw("'' AS base_word"), 'words as word', 'words_searchable', 'reading', DB::raw("'' AS base_word_reading"), 'stage', 'translation', DB::raw("-1 AS read_count"), DB::raw("-1 AS lookup_count"), 'added_to_srs', DB::raw("'phrase' AS type"))
            ->where('user_id', $userId)
            ->where('language', $language);

        if ($text !== 'anytext') {
            $phraseSearch = $phraseSearch->where(function($query) use ($text) {
                $query->orWhere('words_searchable', 'like', '%' . $text . '%')
                    ->orWhere('reading', 'like', '%' . $text . '%');
            });
        }

        if ($bookId !== -1) {
            $phraseSearch->whereIn('id', $filteredPhraseIds);
        }

        if ($stage !== -999) {
            $phraseSearch = $phraseSearch->where('stage', $stage);
        }

        if ($translation == 'not empty') {
            $phraseSearch = $phraseSearch->where('translation', '<>', '');
        }

        if ($phrases == 'only words') {
            $search = $wordSearch;
        } else if ($phrases == 'only phrases') {
            $search = $phraseSearch;
        } else {  
            $search = $wordSearch->union($phraseSearch);
        }

        if ($orderBy == 'words') {
            $search = $search->orderBy('word');
        }

        if ($orderBy == 'words desc') {
            $search = $search->orderBy('word', 'desc');
        }

        if ($orderBy == 'stage') {
            $search = $search->orderBy('stage');
        }

        if ($orderBy == 'stage desc') {
            $search = $search->orderBy('stage', 'desc');
        }

        $search = $search->orderBy('id')->orderBy('type');

        return $search;
    }

    public function searchKanji($userId, $language, $groupBy, $showUnknown) {
        $words = EncounteredWord
            ::where('user_id', $userId)
            ->where('stage', 0)
            ->where('language', $language)
            ->where('kanji', '<>', '')
            ->get();
        
        // get knwon kanji
        $knownKanji = [];
        foreach ($words as $word) {
            $wordKanji = preg_split("//u", $word->kanji, -1, PREG_SPLIT_NO_EMPTY);
            foreach($wordKanji as $currentKanji) {
                if(!in_array($currentKanji, $knownKanji, true)) {
                    array_push($knownKanji, $currentKanji);
                }
            }
        }

        // get kanji list
        if ($groupBy == 'grade') {
            $kanji = Kanji::where(function($query) use($knownKanji) {
                $query->where('grade', '>', 0)->orWhereIn('kanji', $knownKanji);
            });
        } else {
            $kanji = Kanji::where(function($query) use($knownKanji) {
                $query->where('jlpt', '>', 0)->orWhereIn('kanji', $knownKanji);
            });
        }

        if (!$showUnknown) {
            $kanji = $kanji->whereIn('kanji', $knownKanji);
        }
        
        $kanji = $kanji->get();

        // label kanji list
        foreach ($kanji as $currentKanji) {
            $currentKanji->known = in_array($currentKanji->kanji, $knownKanji);
        }

        // group kanji list
        if ($groupBy == 'grade') {
            $kanji = $kanji->groupBy('grade');
        } else {
            $kanji = $kanji->groupBy('jlpt');
        }
        

        // get count for statistics
        if ($groupBy == 'grade') {
            $totalCount = Kanji
                ::select('grade', DB::raw('count(id) as total'))
                ->groupBy('grade')
                ->get()
                ->keyBy('grade');

            $knownCount = Kanji
                ::select('grade', DB::raw('count(id) as total'))
                ->whereIn('kanji', $knownKanji)->groupBy('grade')
                ->get()
                ->keyBy('grade');
        } else {
            $totalCount = Kanji
                ::select('jlpt', DB::raw('count(id) as total'))
                ->groupBy('jlpt')
                ->get()
                ->keyBy('jlpt');

            $knownCount = Kanji
                ::select('jlpt', DB::raw('count(id) as total'))
                ->whereIn('kanji', $knownKanji)->groupBy('jlpt')
                ->get()
                ->keyBy('jlpt');
        }
        
        $searchResults = new \stdClass();
        $searchResults->kanji = $kanji;
        $searchResults->total = $totalCount;
        $searchResults->known = $knownCount;

        return $searchResults;
    }

    public function getKanjiDetails($userId, $kanjiCharacter) {
        $kanjiData = Kanji
            ::where('kanji', $kanjiCharacter)
            ->first();
        
        if (!$kanjiData) {
            throw new \Exception('Kanji not found in database.');
        }

        $words = EncounteredWord
            ::where('word', 'like', '%' . $kanjiCharacter . '%')
            ->where('user_id', $userId)
            ->limit(12)
            ->get();

        $radicals = Radical
            ::select('radicals')
            ->where('kanji', $kanjiCharacter)
            ->first();
        
        $kanjiDetails = new \stdClass();
        $kanjiDetails->kanji = $kanjiData;
        $kanjiDetails->radicals = $radicals->radicals;
        $kanjiDetails->words = $words;

        return $kanjiDetails;
    }
}