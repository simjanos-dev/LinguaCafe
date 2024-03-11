<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Services\ImportService;
use Illuminate\Http\Request;

// request classes
use App\Http\Requests\Import\GetWebsiteTextRequest;
use App\Http\Requests\Import\ImportRequest;
use App\Http\Requests\Import\GetYoutubeSubtitlesRequest;
use App\Http\Requests\Import\GetSubtitleFileContentRequest;

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

    public function import(ImportRequest $request) {
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
            try {
                $fileName = $this->importService->moveFileToTempFolder($userId, $importFile);
            } catch (\Exception $e) {
                abort(500, $e->getMessage());
            }
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
        } catch (\Exception $e) {
            // delete temp file
            if (isset($importFile)) {
                $this->importService->deleteTempFile($fileName);
            }

            abort(500, $e->getMessage());
        }

        // delete temp file
        if (isset($importFile)) {
            $this->importService->deleteTempFile($fileName);
        }

        return response()->json('The text has been imported successfully.', 200);
    }

    public function getYoutubeSubtitles(GetYoutubeSubtitlesRequest $request) {
        $url = $request->post('url');

        try {
            $subtitleList = $this->importService->getYoutubeSubtitles($url);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return response()->json($subtitleList, 200);
    }

    public function getSubtitleFileContent(GetSubtitleFileContentRequest $request) {
        $subtitleFile = $request->file('subtitleFile');
        $userId = Auth::user()->id;

        // move file to temp folder
        try {
            $fileName = $this->importService->moveFileToTempFolder($userId, $subtitleFile);
            
            // get subtitle content
            $subtitleContent = $this->importService->getSubtitleFileContent(storage_path('app/temp') . '/' . $fileName);
        } catch (\Exception $e) {
            $this->importService->deleteTempFile($fileName);
            abort(500, $e->getMessage());
        }

        // delete temp file
        $this->importService->deleteTempFile($fileName);

        return response()->json($subtitleContent, 200);
    }

    public function getWebsiteText(GetWebsiteTextRequest $request) {
        $url = $request->post('url');
        
        try {
            $websiteText = $this->importService->getWebsiteText($url);
        } catch(\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($websiteText, 200);
    }
}
