<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\AnkiApiService;
use \Exception;

class AnkiController extends Controller
{
    protected $ankiApiService = '';
    
    public function __construct() {
        $this->ankiApiService = new AnkiApiService();    
    }

    public function addCardToAnki(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        $word = $request->post('word') ? $request->post('word') : 'empty word error';
        $word = mb_strtolower($word);
        $reading = $request->post('reading') ? $request->post('reading') : '';
        $translation = $request->post('translation') ? $request->post('translation') : '';
        $exampleSentence = $request->post('exampleSentence') ? $request->post('exampleSentence') : '';

        $testResult = $this->ankiApiService->addWord($selectedLanguage, $word, $reading, $translation, $exampleSentence);
        
        return $testResult;
    }
}