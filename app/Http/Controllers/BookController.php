<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Carbon\Carbon;
use App\Models\EncounteredWord;
use App\Models\Book;
use App\Models\Lesson;

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getBooks() {
        $books = new \stdClass();
        $selectedLanguage = Auth::user()->selected_language;
        $books = Book::
            where('user_id', Auth::user()->id)
            ->where('language', $selectedLanguage)
            ->orderBy('updated_at', 'DESC')
            ->get();

        for ($i = 0; $i < count($books); $i++) {
            $books[$i]->wordCount = null;
        }

        return json_encode($books);
    }

    public function getBookWordCounts($bookId) {
        $user = Auth::user();
        $selectedLanguage = $user->selected_language;

        // Get words for calculating word counts
        $words = EncounteredWord
            ::where('user_id', $user->id)
            ->where('language', $selectedLanguage)
            ->pluck(['id', 'word', 'stage'])
            ->keyBy('id')
            ->toArray();

        // Get book or return error if not found
        $book = Book
            ::where('user_id', $user->id)
            ->find($bookId) ?? abort(404, 'Book not found');

        // Calculate word counts
        $wordCounts = $book->getWordCounts($words);

        return json_encode($wordCounts);
    }

    public function saveBook(Request $request) {
        $userId = Auth::user()->id;
        $selectedLanguage = Auth::user()->selected_language;
        $bookId = $request->post('bookId');
        $bookName = $request->post('bookName');

        try {
            if ($bookId == -1) {
                $book = new Book();
                $book->user_id = $userId;
                $book->cover_image = 'default.jpg';
                $book->language = $selectedLanguage;
            } else {
                $book = Book
                    ::where('user_id', $userId)
                    ->where('id', $bookId)
                    ->first();

                if (!$book) {
                    return 'error';
                }
            }

            $book->name = $bookName;
            $book->save();
            
            if (!is_null($request->bookCover)) {
                // delete old image
                $this->deleteBookCover($book);

                // save image on server
                $timestamp = implode('_', explode(' ', Carbon::now()->toDateTimeString()));
                $fileName = $book->id . '_' . $timestamp . '.' . ($request->file('bookCover')->getClientOriginalExtension());
                $request->file('bookCover')->storeAs('/images/book_images/', $fileName);

                // save image in database
                $book->cover_image = $fileName;
                $book->save();
            }
        } catch (Throwable $e) {
            return 'error';
        } catch (Exception $e) {
            return 'error';
        }
        
        return 'success';
    }

    public function deleteBook(Request $request) {
        $bookId = $request->post('bookId');
        $userId = Auth::id();

        Lesson
            ::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->delete();

        $book = Book
            ::where('user_id', $userId)
            ->find($bookId);

        if ($book) {
            $this->deleteBookCover($book);
            $book->delete();
        }

        return 'success';
    }

    private function deleteBookCover($book) {
        if ($book->cover_image !== '' && $book->cover_image !== 'default.jpg') {
            Storage::delete('/images/book_images/' . $book->cover_image);
        }
    }

}