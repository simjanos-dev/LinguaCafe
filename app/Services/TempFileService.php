<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class TempFileService {

    // moves the uploaded file to the temp folder, and returns the filename
    public function moveFileToTempFolder($userId, $importFile) {
        $randomString = bin2hex(openssl_random_pseudo_bytes(30));
        $extension = '.' . $importFile->getClientOriginalExtension();
        $fileName = $userId . '_' . $randomString . $extension;
        $importFile->move(storage_path('app/temp'), $fileName);

        return $fileName;
    }

    public function deleteTempFile($fileName) {
        File::delete(storage_path('app/temp') . '/' . $fileName);
        return true;
    }
}