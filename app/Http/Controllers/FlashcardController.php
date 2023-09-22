<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EncounteredWord;
use App\Models\FlashcardCollection;
use App\Models\Flashcard;

class FlashcardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function getFlashcardCollections() {
        $selectedLanguage = Auth::user()->selected_language;
        $flashcardCollections = FlashcardCollection::where('language', $selectedLanguage)->where('user_id', Auth::user()->id)->get();

        return json_encode($flashcardCollections);
    }

    public function getFlashcardCollection(Request $request) {
        $flashcardCollection = FlashcardCollection::where('id', $request->flashcardCollectionId)->where('user_id', Auth::user()->id)->first();
        $flashcards = Flashcard::where('flash_card_collection_id', $request->flashcardCollectionId)->get();

        $data = new \stdClass();
        $data->flashcardCollection = $flashcardCollection;
        $data->flashcards = $flashcards;
        $data->language = Auth::user()->selected_language;

        return json_encode($data);
    }

    public function saveFlashcardCollection(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        if ($request->flashCardCollectionId !== -1) {
            $flashCardCollection = FlashcardCollection::where('id', $request->flashCardCollectionId)->where('user_id', Auth::user()->id)->first();
        } else {
            $flashCardCollection = new FlashcardCollection();
            $flashCardCollection->user_id = Auth::user()->id;
        }
        
        $flashCardCollection->name = $request->name;
        $flashCardCollection->type = $request->type;
        $flashCardCollection->language = $selectedLanguage;
        $flashCardCollection->save();

        // save flash cards
        foreach (json_decode($request->flashCards) as $flashCard) {
            if ($flashCard->id == -1) {
                $currentFlashcard = new Flashcard();
                $currentFlashcard->level = 1;
                $currentFlashcard->reading = '';
                $currentFlashcard->last_reviewed = null;
            } else {
                $currentFlashcard = Flashcard::where('id', $flashCard->id)->where('user_id', Auth::user()->id)->first();
                if ($flashCard->deleted) {
                    $currentFlashcard->delete();
                }
            }

            if (!$flashCard->deleted) {
                $currentFlashcard->user_id = Auth::user()->id;
                $currentFlashcard->flash_card_collection_id = $flashCardCollection->id;
                $currentFlashcard->language = $flashCardCollection->language;
                $currentFlashcard->sentence_raw = $flashCard->sentence;
                $currentFlashcard->sentence_processed = '';
                $currentFlashcard->reading = $flashCard->reading;
                $currentFlashcard->level = $flashCard->level;
                $currentFlashcard->translation = $flashCard->translation;
                $currentFlashcard->save();
            }
        }
        
        return 'success';
    }
    
    public function deleteFlashcardCollection(Request $request) {
        $flashcardCollection = FlashcardCollection::where('id', $request->flashcardCollectionId)->where('user_id', Auth::user()->id)->first();
        Flashcard::where('flash_card_collection_id', $request->flashcardCollectionId)->delete();
        $flashcardCollection->delete();

        return 'success';
    }
    
}

