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

    public function import(Request $request) {
        $userId = Auth::user()->id;
        $importType = $request->post('importType');
        $textProcessingMethod = $request->post('textProcessingMethod');
        $bookId = $request->post('bookId');
        $bookName = $request->post('bookName');
        $chapterName = $request->post('chapterName');
        
        if ($importType == 'e-book') {
            $importFile = $request->file('importFile');
        } else {
            $importText = $request->post('importText');
        }
        

        // move file to temp folder
        if ($importType === 'e-book') {
            $randomString = bin2hex(openssl_random_pseudo_bytes(30));
            $extension = '.' . $importFile->getClientOriginalExtension();
            $fileName = $userId . '_' . $randomString . $extension;
            $importFile->move(storage_path('app/temp'), $fileName);
        }

        // import
        try {
            if ($importType === 'e-book') {
                // e-book
                (new ImportService())->importBook($textProcessingMethod, storage_path('app/temp') . '/' . $fileName, $bookId, $bookName, $chapterName);
            } else {
                // text
                (new ImportService())->importText($textProcessingMethod, $importText, $bookId, $bookName, $chapterName);
            }
        } catch (\Exception $exception) {
            if ($importType === 'e-book') {
                File::delete(storage_path('app/temp') . '/' . $fileName);
            }

            return $exception->getMessage();
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

        return json_encode($subtitleList);
    }
}
