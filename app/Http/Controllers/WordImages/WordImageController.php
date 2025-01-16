<?php

namespace App\Http\Controllers\WordImages;

use App\Models\Phrase;
use Illuminate\Http\Request;
use App\Models\EncounteredWord;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\WordImages\WordImageService;
use App\Http\Requests\Images\WordImages\UploadWordImageRequest;
use App\Http\Requests\Images\WordImages\UploadPhraseImageRequest;
use App\Http\Requests\Images\WordImages\SetWordImageFromUrlRequest;
use App\Http\Requests\Images\WordImages\SetPhraseImageFromUrlRequest;

class WordImageController extends Controller
{
    public function __construct(
        public WordImageService $wordImageService,
    ) {
        //
    }

    public function setWordImageFromUrl(SetWordImageFromUrlRequest $request, EncounteredWord $word)
    {
        $url = $request->validated('url');
        $user = Auth::user();

        $fileName = $this->wordImageService->setImageFromUrl($word, $user, $url);

        return response()->json([
            'data' => [
                'image' => $fileName,
            ],
        ]);
    }
    
    public function setPhraseImageFromUrl(SetPhraseImageFromUrlRequest $request, Phrase $phrase)
    {
        $url = $request->validated('url');
        $user = Auth::user();

        $fileName = $this->wordImageService->setImageFromUrl($phrase, $user, $url);

        return response()->json([
            'data' => [
                'image' => $fileName,
            ],
        ]);
    }

    public function uploadWordImage(UploadWordImageRequest $request, EncounteredWord $word)
    {
        $imageFile = $request->file('imageFile');
        $user = Auth::user();

        $fileName = $this->wordImageService->uploadImage($user, $word, $imageFile);

        return response()->json([
            'data' => [
                'image' => $fileName,
            ],
        ]);
    }

    public function uploadPhraseImage(UploadPhraseImageRequest $request, Phrase $phrase)
    {

    }

    public function getWordImage(EncounteredWord $word) 
    {
        $user = Auth::user();

        $imagePath = $this->wordImageService->getImagePath($user, $word);

        return response()->file($imagePath);
    }

    public function getPhraseImage(Phrase $phrase) 
    {
        $user = Auth::user();

        $imagePath = $this->wordImageService->getImagePath($user, $phrase);

        return response()->file($imagePath);
    }
}
