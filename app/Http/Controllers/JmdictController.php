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
use App\Services\DictionaryImportService;

class JmdictController extends Controller
{
    /*
        Converts jmdict file to text from xml. 
        Should be moved to python in the future, so this can be deleted.
    */
    public function jmdictXmlToText() {
        (new DictionaryImportService())->jmdictXmlToText();
    }
}
