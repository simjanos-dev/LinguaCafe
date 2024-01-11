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
        $word = mb_strtolower($request->post('word', 'empty word error'));
        $reading = $request->post('reading', '');
        $translation = $request->post('translation', '');
        $exampleSentence = $request->post('exampleSentence', '');

        return $this->ankiApiService->addWord($selectedLanguage, $word, $reading, $translation, $exampleSentence);
    }
}