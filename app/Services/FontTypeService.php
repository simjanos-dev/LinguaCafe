<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\FontType;

class FontTypeService {
    function __construct() {
    }

    /*
        Scans the fonts directory for a list of available font types. It also returns 
        a list of enabled languages for them.
    */
    public function getInstalledFontTypes() {
        $fonts = FontType::get();
        return $fonts;
    }

    public function uploadfontType($fontFile, $fontName, $fontLanguages) {
        // upload file
        $fontFile->move(storage_path('app/fonts'), $fontFile->getClientOriginalName());

        // create font in database
        
        return true;
    }
}