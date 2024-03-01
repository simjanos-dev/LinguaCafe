<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use \Exception;
use Carbon\Carbon;
use App\Models\TextBlock;
use App\Models\EncounteredWord;
use App\Models\Phrase;
use App\Models\Kanji;
use App\Models\Radical;
use App\Models\Book;
use App\Models\Lesson;
use App\Models\ExampleSentence;
use App\Models\Goal;
use App\Models\GoalAchievement;

// services
use App\Services\VocabularyService;

// request classes
use App\Http\Requests\Vocabulary\GetUniqueWordRequest;
use App\Http\Requests\Vocabulary\UpdateWordRequest;
use App\Http\Requests\Vocabulary\CreatePhraseRequest;
use App\Http\Requests\Vocabulary\UpdatePhraseRequest;
use App\Http\Requests\Vocabulary\GetPhraseRequest;
use App\Http\Requests\Vocabulary\DeletePhraseRequest;
use App\Http\Requests\Vocabulary\GetExampleSentenceRequest;
use App\Http\Requests\Vocabulary\CreateOrUpdateExampleSentenceRequest;
use App\Http\Requests\Vocabulary\SearchVocabularyRequest;
use App\Http\Requests\Vocabulary\ExportToCsvRequest;

class VocabularyController extends Controller
{
    private $vocabularyService;

    public function __construct(VocabularyService $vocabularyService) {
        $this->vocabularyService = $vocabularyService;
    }


    public function getUniqueWord($wordId, GetUniqueWordRequest $request) {
        $userId = Auth::user()->id;

        try {
            $word = $this->vocabularyService->getUniqueWord($userId, $wordId);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }

        return response()->json($word, 200);
    }

    public function updateWord(UpdateWordRequest $request) {
        $userId = Auth::user()->id;
        $wordId = $request->post('id');
        $wordData = [];
        $wordStage = null;

        if ($request->has('translation')) {
            $wordData['translation'] = $request->translation === NULL ? '' : $request->translation;
        }

        if ($request->has('reading')) {
            $wordData['reading'] = $request->reading === NULL ? '' : $request->reading;
        }

        if ($request->has('base_word')) {
            $wordData['base_word'] = $request->base_word === NULL ? '' : $request->base_word;
        }

        if ($request->has('base_word_reading')) {
            $wordData['base_word_reading'] = $request->base_word_reading === NULL ? '' : $request->base_word_reading;
        }

        if (isset($request->lookup_count)) {
            $wordData['lookup_count'] = $request->lookup_count;
        }

        if (isset($request->read_count)) {
            $wordData['read_count'] = $request->read_count;
        }

        if (isset($request->relearning)) {
            $wordData['relearning'] = boolval($request->relearning);
        }

        if (isset($request->stage)) {
            $wordStage = $request->stage;
        }

        try {
            $this->vocabularyService->updateWord($userId, $wordId, $wordData, $wordStage);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }

        return response()->json('Word has been successfully updated.', 200);
    }

    public function getPhrase($phraseId, GetPhraseRequest $request) {
        $userId = Auth::user()->id;

        try {
            $phrase = $this->vocabularyService->getPhrase($userId, $phraseId);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }

        return response()->json($phrase, 200);
    }

    public function createPhrase(CreatePhraseRequest $request) {
        $userId = Auth::user()->id;
        $language = Auth::user()->selected_language;
        $words = json_decode($request->words);
        $stage = $request->stage;
        $reading = is_null($request->reading) ? '' : $request->reading;
        $translation = is_null($request->translation) ? '' : $request->translation;

        try {
            $phraseId = $this->vocabularyService->createPhrase($userId, $language, $words, $stage, $reading, $translation);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }

        return response()->json($phraseId, 200);
    }

    public function updatePhrase(UpdatePhraseRequest $request) {
        $userId = Auth::user()->id;
        $phraseId = $request->post('id');
        $phraseData = [];
        $phraseStage = null;

        if ($request->has('translation')) {
            $phraseData['translation'] = $request->translation === NULL ? '' : $request->translation;
        }

        if ($request->has('reading')) {
            $phraseData['reading'] = $request->reading === NULL ? '' : $request->reading;
        }

        if (isset($request->lookup_count)) {
            $phraseData['lookup_count'] = $request->lookup_count;
        }

        if (isset($request->relearning)) {
            $phraseData['relearning'] = boolval($request->relearning);
        }

        if (isset($request->stage)) {
            $phraseStage = $request->stage;
        }

        try {
            $this->vocabularyService->updatePhrase($userId, $phraseId, $phraseData, $phraseStage);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }

        return response()->json('Phrase has been successfully updated.', 200);
    }

    public function deletePhrase(DeletePhraseRequest $request) {
        $userId = Auth::user()->id;
        $language = Auth::user()->selected_language;
        $phraseId = $request->post('phraseId');
        
        try {
            $this->vocabularyService->deletePhrase($userId, $language, $phraseId);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }

        return response()->json('Phrase has been successfully deleted.', 200);
    }

    public function getExampleSentence($targetType, $targetId, GetExampleSentenceRequest $request) {
        $userId = Auth::user()->id;
        
        try {
            $exampleSentence = $this->vocabularyService->getExampleSentence($userId, $targetType, $targetId);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }

        return response()->json($exampleSentence, 200);
    }

    public function createOrUpdateExampleSentence(CreateOrUpdateExampleSentenceRequest $request) {
        $language = Auth::user()->selected_language;
        $userId = Auth::user()->id;
        $targetType = $request->targetType;
        $targetId = $request->targetId;
        $exampleSentenceWords = json_decode($request->exampleSentenceWords);

        try {
            $this->vocabularyService->createOrUpdateExampleSentence($userId, $language, $targetType, $targetId, $exampleSentenceWords);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }

        return response()->json('Example sentence has been successfully saved.', 200);
    }

    public function searchVocabulary(SearchVocabularyRequest $request) {
        $userId = Auth::user()->id;
        $language = Auth::user()->selected_language;
        $text = $request->text;
        $bookId = $request->book;
        $chapterId = $request->chapter;
        $stage = $request->stage;
        $phrases = $request->phrases;
        $orderBy = $request->orderBy;
        $translation = $request->translation;
        $page = $request->page; 

        try {
            $searchResults = $this->vocabularyService->searchVocabulary($userId, $language, $text, $bookId, $chapterId, $stage, $phrases, $orderBy, $translation, $page);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }

        return response()->json($searchResults, 200);
    }

    public function exportToCsv(ExportToCsvRequest $request) {
        $userId = Auth::user()->id;
        $language = Auth::user()->selected_language;
        $text = $request->post('text');
        $bookId = $request->post('book');
        $chapterId = $request->post('chapter');
        $stage = $request->post('stage');
        $phrases = $request->post('phrases');
        $orderBy = $request->post('orderBy');
        $translation = $request->post('translation');
        $fields = $request->post('fields');

        try {
            $csv = $this->vocabularyService->exportToCsv($userId, $language, $text, $bookId, $chapterId, $stage, $phrases, $orderBy, $translation, $fields);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }

        $csv->output('vocabulary.csv');
        return response('', 200);
    }

    public function searchKanji(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        $words = EncounteredWord::where('user_id', Auth::user()->id)->where('stage', 0)->where('language', $selectedLanguage)->where('kanji', '<>', '')->get();
        
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
        if ($request->groupBy == 'grade') {
            $kanji = Kanji::where(function($query) use($knownKanji) {
                $query->where('grade', '>', 0)->orWhereIn('kanji', $knownKanji);
            });
        } else {
            $kanji = Kanji::where(function($query) use($knownKanji) {
                $query->where('jlpt', '>', 0)->orWhereIn('kanji', $knownKanji);
            });
        }

        if (!$request->showUnknown) {
            $kanji = $kanji->whereIn('kanji', $knownKanji);
        }
        
        $kanji = $kanji->get();

        // label kanji list
        foreach ($kanji as $currentKanji) {
            $currentKanji->known = in_array($currentKanji->kanji, $knownKanji);
        }

        // group kanji list
        if ($request->groupBy == 'grade') {
            $kanji = $kanji->groupBy('grade');
        } else {
            $kanji = $kanji->groupBy('jlpt');
        }
        

        // get count for statistics
        if ($request->groupBy == 'grade') {
            $totalCount = Kanji::select('grade', DB::raw('count(id) as total'))->groupBy('grade')->get()->keyBy('grade');
            $knownCount = Kanji::select('grade', DB::raw('count(id) as total'))->whereIn('kanji', $knownKanji)->groupBy('grade')->get()->keyBy('grade');
        } else {
            $totalCount = Kanji::select('jlpt', DB::raw('count(id) as total'))->groupBy('jlpt')->get()->keyBy('jlpt');
            $knownCount = Kanji::select('jlpt', DB::raw('count(id) as total'))->whereIn('kanji', $knownKanji)->groupBy('jlpt')->get()->keyBy('jlpt');
        }
        
        $data = new \stdClass();
        $data->kanji = $kanji;
        $data->total = $totalCount;
        $data->known = $knownCount;

        return json_encode($data);
    }

    public function getKanjiDetails(Request $request) {
        $kanji = Kanji::where('kanji', $request->kanji)->first();
        $words = EncounteredWord::where('word', 'like', '%' . $request->kanji . '%')->limit(12)->get();
        $radicals = Radical::select('radicals')->where('kanji', $request->kanji)->first();
        
        $data = new \stdClass();
        $data->kanji = $kanji;
        $data->radicals = $radicals->radicals;
        $data->words = $words;

        return json_encode($data);
    }
}
