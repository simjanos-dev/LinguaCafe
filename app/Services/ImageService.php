<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use App\Models\Book;

class ImageService {
    
    public function __construct() {
    }

    /*
        Checks if the user is authorized to download the image,
        and returns the absolute file path, or throws an exception.
    */
    public function getBookImage($userId, $fileName) {
        
        $book = Book
            ::where('user_id', $userId)
            ->where('cover_image', $fileName)
            ->first();

        if (!$book && $fileName !== null) {
            abort(500, 'The file does not exist, or it belongs to a different user.');
        }

        if (is_null($fileName)) {
            return Storage::disk('default-files')->path('/images/book_images/default.svg');
        } else {
            return Storage::path('/images/book_images/' . $fileName);
        }
    }
}