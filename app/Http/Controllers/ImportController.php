<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Services\ImportService;
use Illuminate\Http\Request;


class ImportController extends Controller
{
    private $importMethods = [
        'e-book' => 'e-book',
        'jellyfin-subtitle' => 'subtitle',
        'plain-text' => 'text',
        'text-file' => 'text',
        'youtube' => 'text',
    ];

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
        if ($importMethod === 'e-book') {
            $randomString = bin2hex(openssl_random_pseudo_bytes(30));
            $extension = '.' . $importFile->getClientOriginalExtension();
            $fileName = $userId . '_' . $randomString . $extension;
            $importFile->move(storage_path('app/temp'), $fileName);
        }

        // import
        try {
            if ($importMethod === 'e-book') {
                // e-book
                (new ImportService())->importBook($chunkSize, $textProcessingMethod, storage_path('app/temp') . '/' . $fileName, $bookId, $bookName, $chapterName);
            } else if ($importMethod === 'text') {
                // text
                (new ImportService())->importText($chunkSize, $textProcessingMethod, $importText, $bookId, $bookName, $chapterName);
            } else if ($importMethod === 'subtitle') {
                // text
                (new ImportService())->importSubtitles($chunkSize, $textProcessingMethod, $importSubtitles, $bookId, $bookName, $chapterName);
            }
        } catch (\Exception $exception) {
            if ($importMethod === 'e-book') {
                File::delete(storage_path('app/temp') . '/' . $fileName);
            }

            return $exception;
        }

        // delete temp file
        if ($importType === 'e-book') {
            File::delete(storage_path('app/temp') . '/' . $fileName);
        }

        return 'success';
    }

    public function getYoutubeSubtitles(Request $request) {
        $url = $request->post('url');
        $subtitleList = (new ImportService())->getYoutubeSubtitles($url);

        return $subtitleList;
    }
}
