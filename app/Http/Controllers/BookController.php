<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EncounteredWord;
use App\Models\Book;

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getBooks() {
        $books = new \StdClass();

        $selectedLanguage = Auth::user()->selected_language;
        $books = Book::where('language', $selectedLanguage)->where('user_id', Auth::user()->id)->orderBy('updated_at', 'DESC')->get();
        $words = EncounteredWord::select(['id', 'word', 'stage'])->where('user_id', Auth::user()->id)->where('language', Auth::user()->selected_language)->get()->keyBy('id')->toArray();

        for ($i = 0; $i < count($books); $i++) {
            $books[$i]->wordCount = $books[$i]->getWordCounts($words);
        }

        return json_encode($books);
    }

    public function createBook(Request $request) {
        try {
            $book = new Book();
            $book->user_id = Auth::user()->id;
            $book->name = $request->name;
            $book->cover_image = '';
            $book->language = Auth::user()->selected_language;
            $book->save();
            
            if (!is_null($request->image)) {
                // save image on server
                $fileName = $book->id . '.' . ($request->file('image')->getClientOriginalExtension());
                $path = $request->file('image')->storeAs('/images/book_images/', $fileName);

                // save image in database
                $book->cover_image = $fileName;
                $book->save();
            }
        } catch (Throwable $e) {
            return 'error';
        }
        
        return 'success';
    }
}