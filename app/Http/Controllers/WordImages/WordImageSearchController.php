<?php

namespace App\Http\Controllers\WordImages;

use App\Http\Controllers\Controller;
use App\Services\WordImages\WordImageSearchService;

class WordImageSearchController extends Controller
{
    public function search(string $searchEngine, string $searchTerm) {
        
        $images = (new WordImageSearchService())->search($searchTerm);

        return [
            'data' => $images,
        ];
    }
}
