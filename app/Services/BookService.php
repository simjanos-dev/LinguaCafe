<?php

namespace App\Services;

use App\Models\Book;
use App\Models\EncounteredWord;

class BookService
{
    public function __construct()
    {
    }
    
    public function getBooks($userId, $language) {
        $books = Book
            ::where('user_id', $userId)
            ->where('language', $language)
            ->orderBy('updated_at', 'DESC')
            ->get();

        // sets initial value used by vue in the library
        foreach ($books as $book) {
            $book->wordCount = null;
        }

        return $books;
    }


    public function getBookWordCounts($userId, $bookId) {
        $book = Book
            ::where('user_id', $userId)
            ->where('id', $bookId)
            ->first();
        
        
        if (!$book) {
            throw new \Exception('Book does not exist, or it belongs to a different user.');
        }
        
        // get words for calculating word counts
        $words = EncounteredWord
            ::select(['id', 'word', 'stage'])
            ->where('user_id', $userId)
            ->where('language', $book->language)
            ->get()
            ->keyBy('id')
            ->toArray();

        // calculate word counts
        return $book->getWordCounts($userId, $words);
    }
}