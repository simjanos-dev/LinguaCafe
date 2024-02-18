<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

// request classes
use App\Http\Requests\Book\GetBookWordCountsRequest;

// services
use App\Services\BookService;

// models
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
        $userId = Auth::user()->id;
        $language = Auth::user()->selected_language;
        
        $books = (new BookService())->getBooks($userId, $language);

        return response()->json($books, 200);
    }

    public function getBookWordCounts($bookId, GetBookWordCountsRequest $request) {
        $userId = Auth::user()->id;
        $language = Auth::user()->selected_language;

        try {
            $wordCounts = (new BookService())->getBookWordCounts($userId, $bookId);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }

        return response()->json($wordCounts, 200);
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
                if ($book->cover_image !== '' && $book->cover_image !== 'default.jpg') {
                    Storage::delete('/images/book_images/' . $book->cover_image);
                }

                // save image on server
                $timestamp = implode('_', explode(' ', Carbon::now()->toDateTimeString()));
                $fileName = $book->id . '_' . $timestamp . '.' . ($request->file('bookCover')->getClientOriginalExtension());
                $request->file('bookCover')->storeAs('/images/book_images/', $fileName);

                // save image in database
                $book->cover_image = $fileName;
                $book->save();
            }
        } catch (\Throwable $e) {
            return 'error';
        } catch (\Exception $e) {
            return 'error';
        }
        
        return 'success';
    }

    public function deleteBook(Request $request) {
        $bookId = $request->post('bookId');
        $userId = Auth::user()->id;

        $chapters = Lesson
            ::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->delete();
            
        $book = Book
            ::where('user_id', $userId)
            ->where('id', $bookId)
            ->first();
            
        if ($book->cover_image !== '' && $book->cover_image !== 'default.jpg') {
            Storage::delete('/images/book_images/' . $book->cover_image);
        }

        Book
            ::where('user_id', $userId)
            ->where('id', $bookId)
            ->delete();
        return 'success';
    }
}