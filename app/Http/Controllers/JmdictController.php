<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kanji;
use App\Models\Radical;
use App\Models\Lesson;
use App\Models\VocabularyJmdict;
use App\Models\VocabularyJmdictWord;
use App\Models\VocabularyJmdictReading;
use Illuminate\Support\Facades\DB;
use App\Services\JmdictImportService;

class JmdictController extends Controller
{
    public function xmlToText() {
        (new JmdictImportService())->xmlToText();
    }

    public function importJmdict() {
        (new JmdictImportService())->jmdictImport();
    }

    public function importKanji() {
        (new JmdictImportService())->kanjiImport();
    }

    public function importRadicals() {
        (new JmdictImportService())->kanjiRadicalImport();
    }
}
