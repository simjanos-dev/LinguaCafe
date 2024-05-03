<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

// form requests
use App\Http\Requests\Image\GetBookImageRequest;
use App\Http\Requests\Image\GetKanjiImageRequest;

// services
use App\Services\ImageService;

class ImageController extends Controller
{
    private $imageService;

    public function __construct(ImageService $imageService) {
        $this->middleware('auth');
        $this->imageService = $imageService;
    }
    
    public function getBookImage($fileName, GetBookImageRequest $request) {
        $userId = Auth::user()->id;

        try {
            $imagePath = $this->imageService->getBookImage($userId, $fileName);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
        
        return response()->file($imagePath);
    }

    public function getKanjiImage($fileName, GetKanjiImageRequest $request) {
        $imagePath = Storage::path('/images/kanjivg/' . $fileName);
        return response()->file($imagePath);
    }
}
