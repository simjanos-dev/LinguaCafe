<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Services\ImportService;
use Illuminate\Http\Request;

// request classes
use App\Http\Requests\Import\GetWebsiteTextRequest;

class ImportController extends Controller {
    private $importMethods = [
        'e-book' => 'e-book',
        'jellyfin-subtitle' => 'subtitle',
        'subtitle-file' => 'subtitle',
        'plain-text' => 'text',
        'text-file' => 'text',
        'youtube' => 'text',
        'website' => 'text',
    ];

    private $importService;

    public function __construct(ImportService $importService) {
        $this->importService = $importService;
    }

    public function import(Request $request) {
        $userId = Auth::user()->id;
        $importType = $request->post('importType');
        $textProcessingMethod = $request->post('textProcessingMethod');
        $bookId = $request->post('bookId');
        $bookName = $request->post('bookName');
        $chapterName = $request->post('chapterName');
        $chunkSize = intval($request->post('maximumCharactersPerChapter'));
        $importMethod = $this->importMethods[$importType];

        if ($importMethod == 'e-book') {
            $importFile = $request->file('importFile');
        } else if ($importMethod == 'text') {
            $importText = $request->post('importText');
        } else if ($importMethod == 'subtitle') {
            $importSubtitles = $request->post('importSubtitles');
        }
        
        // move file to temp folder
        if (isset($importFile)) {
            $randomString = bin2hex(openssl_random_pseudo_bytes(30));
            $extension = '.' . $importFile->getClientOriginalExtension();
            $fileName = $userId . '_' . $randomString . $extension;
            $importFile->move(storage_path('app/temp'), $fileName);
        }

        // import
        try {
            if ($importMethod === 'e-book') {
                // e-book
                $this->importService->importBook($chunkSize, $textProcessingMethod, storage_path('app/temp') . '/' . $fileName, $bookId, $bookName, $chapterName);
            } else if ($importMethod === 'text') {
                // text
                $this->importService->importText($chunkSize, $textProcessingMethod, $importText, $bookId, $bookName, $chapterName);
            } else if ($importMethod === 'subtitle') {
                // text
                $this->importService->importSubtitles($chunkSize, $textProcessingMethod, $importSubtitles, $bookId, $bookName, $chapterName);
            }
        } catch (\Exception $exception) {
            // delete temp file
            if (isset($importFile)) {
                File::delete(storage_path('app/temp') . '/' . $fileName);
            }

            return $exception;
        }

        // delete temp file
        if (isset($importFile)) {
            File::delete(storage_path('app/temp') . '/' . $fileName);
        }

        return 'success';
    }

    public function getYoutubeSubtitles(Request $request) {
        $url = $request->post('url');
        $subtitleList = $this->importService->getYoutubeSubtitles($url);

        return $subtitleList;
    }

    public function getSubtitleFileContent(Request $request) {
        $subtitleFile = $request->file('subtitleFile');
        $userId = Auth::user()->id;        

        // move file to temp folder
        $randomString = bin2hex(openssl_random_pseudo_bytes(30));
        $extension = '.' . $subtitleFile->getClientOriginalExtension();
        $fileName = $userId . '_' . $randomString . $extension;
        $subtitleFile->move(storage_path('app/temp'), $fileName);

        // get subtitle content
        $subtitleContent = $this->importService->getSubtitleFileContent(storage_path('app/temp') . '/' . $fileName);

        // delete temp file
        File::delete(storage_path('app/temp') . '/' . $fileName);

        return $subtitleContent;
    }

    public function getWebsiteText(GetWebsiteTextRequest $request) {
        $url = $request->post('url');
        
        try {
            $websiteText = $this->importService->getWebsiteText($url);
        } catch(\Exception $e) {
            abort(500, $e->getMessage());
        }
        return $websiteText;
    }
}
