<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EncounteredWord;
use App\Models\Phrase;
use App\Models\Kanji;
use App\Models\Radical;
use App\Models\Book;
use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class VocabularyController extends Controller
{
    public function search(Request $request) {
        $wordsToSkip = config('langapp.wordsToSkip');
        $selectedLanguage = Auth::user()->selected_language;
        $results = [];

        // get books and chapters
        $books = Book::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->get();
        $bookIndex = -1;
        for ($i = 0; $i < count($books); $i++) {
            $books[$i]->chapters = Lesson::select(['id', 'name'])->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('book_id', $books[$i]->id)->get();
            
            if (isset($request->book) && $books[$i]->id == $request->book) {
                $bookIndex = $i;
            }
        }

        // filters
        $limit = 30;

        $text = $request->text;
        $bookId = $request->book;
        $chapterId = $request->chapter;
        $stage = $request->stage;
        $phrases = $request->phrases;
        $orderBy = $request->orderBy;
        $translation = $request->translation;
        $page = $request->page;

        // get words and phrases
        // from filtered chapters
        $filteredChapters = Lesson::where('user_id', Auth::user()->id)->where('language', $selectedLanguage);
        $filteredWords = [];
        $filteredPhraseIds = [];
        if ($bookId !== 'any') {
            $filteredChapters = $filteredChapters->where('book_id', $bookId);
        }

        if ($chapterId !== 'any') {
            $filteredChapters = $filteredChapters->where('id', $chapterId);
        }
        
        $filteredChapters = $filteredChapters->get();

        if ($bookId !== 'any') {
            foreach ($filteredChapters as $filteredChapter) {
                // add filtered phrase ids
                $filteredChapterText = json_decode(gzuncompress($filteredChapter->processed_text));
                foreach ($filteredChapterText as $filteredChapterWord) {
                    foreach ($filteredChapterWord->phraseIds as $phraseId) {
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
        $wordSearch = EncounteredWord::select('id', 'word', 'reading', 'stage', 'translation', DB::raw("'word' AS type"))->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->whereNotIn('word', $wordsToSkip);

        if ($text !== 'anytext') {
            $wordSearch = $wordSearch->where(function($query) use ($text) {
                $query->orWhere('word', 'like', '%' . $text . '%')
                    ->orWhere('reading', 'like', '%' . $text . '%');
            });
        }

        if ($bookId !== 'any') {
            $wordSearch->whereIn('word', $filteredWords);
        }

        if ($stage !== 'any') {
            $wordSearch = $wordSearch->where('stage', $stage);
        }

        if ($translation == 'not empty') {
            $wordSearch = $wordSearch->where('translation', '<>', '');
        }
        
        // search for phrases and apply filters
        $phraseSearch = Phrase::select('id', 'words_searchable as word', 'reading', 'stage', 'translation', DB::raw("'phrase' AS type"))->where('user_id', Auth::user()->id)->where('language', $selectedLanguage);

        if ($text !== 'anytext') {
            $phraseSearch = $phraseSearch->where(function($query) use ($text) {
                $query->orWhere('words_searchable', 'like', '%' . $text . '%')
                    ->orWhere('reading', 'like', '%' . $text . '%');
            });
        }

        if ($bookId !== 'any') {
            $phraseSearch->whereIn('id', $filteredPhraseIds);
        }

        if ($stage !== 'any') {
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

        $data = new \StdClass();
        $data->wordCount = $search->count();
        $data->words = $search->skip(($page - 1) * $limit)->take($limit)->get();
        $data->books = $books;
        $data->bookIndex = $bookIndex;
        $data->pageCount = ceil($data->wordCount / $limit);
        $data->currentPage = $page;

        return json_encode($data);
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
        
        $data = new \StdClass();
        $data->kanji = $kanji;
        $data->total = $totalCount;
        $data->known = $knownCount;

        return json_encode($data);
    }

    public function getKanjiDetails(Request $request) {
        $kanji = Kanji::where('kanji', $request->kanji)->first();
        $words = EncounteredWord::where('word', 'like', '%' . $request->kanji . '%')->limit(12)->get();
        $radicals = Radical::select('radicals')->where('kanji', $request->kanji)->first();
        
        $data = new \StdClass();
        $data->kanji = $kanji;
        $data->radicals = $radicals->radicals;
        $data->words = $words;

        return json_encode($data);
    }
}
