<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EncounteredWord;
use App\Models\Book;
use App\Models\Lesson;
use App\Models\Phrase;
use Illuminate\Support\Facades\Auth;

class VocabularyController extends Controller
{
    public function search(Request $request) {
        $wordsToSkip = ['。', '、', ':', '？', '！', '＜', '＞', '：', ' ', '「', '」', '（', '）', '｛', '｝', '≪', '≫', '〈', '〉',
                            '《', '》','【', '】', '『', '』', '〔', '〕', '［', '］', '・', '?', '(', ')', ' ', ' NEWLINE ', '.', '%', '-',
                            '«', '»', "'", '’', '–', 'NEWLINE', 'newline', ' '];

        $selectedLanguage = Auth::user()->selected_language;
        $term = '';
        $bookId = -1;
        $chapterId = -1;
        $stage = -1;
        $page = 1;
        
        // get books and chapters
        $books = Book::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->get();
        for ($i = 0; $i < count($books); $i++) {
            $books[$i]->chapters = Lesson::select(['id', 'name'])->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('book_id', $books[$i]->id)->get();
        }
        
        $search = EncounteredWord::inRandomOrder()->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->whereNotIn('word', $wordsToSkip);

        $search = $search->where('translation', '<>', '')->limit(30)->get();
        
        $data = new \StdClass();
        $data->words = $search;
        $data->books = $books;
        
        return json_encode($data);
    }
}
