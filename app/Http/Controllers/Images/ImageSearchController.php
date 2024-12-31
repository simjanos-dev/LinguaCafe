<?php

namespace App\Http\Controllers\Images;

use App\Http\Controllers\Controller;
use App\Services\Images\ImageSearchService;

class ImageSearchController extends Controller
{
    public function search(string $searchEngine, string $imageType, string $searchTerm) {
        
        $images = (new ImageSearchService())->search($searchTerm);

        return [
            'data' => $images,
        ];
    }
}
