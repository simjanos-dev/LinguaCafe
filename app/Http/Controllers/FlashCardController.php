<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EncounteredWord;
use App\Models\DailyAchivement;
use App\Models\FlashCardCollection;
use App\Models\FlashCard;

class FlashCardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function flashCardCollections() {
        $selectedLanguage = Auth::user()->selected_language;
        $flashCardCollections = FlashCardCollection::where('language', $selectedLanguage)->where('user_id', Auth::user()->id)->get();
        return view('flash_card_collections', [
            'language' => $selectedLanguage,
            'flashCardCollections' => $flashCardCollections
        ]);
    }

    public function createFlashCardCollection() {
        return view('edit_flash_card_collection');
    }

    public function editFlashCardCollection($flashCardCollectionId) {
        $flashCardCollection = FlashCardCollection::where('id', $flashCardCollectionId)->where('user_id', Auth::user()->id)->first();
        $flashCards = FlashCard::where('flash_card_collection_id', $flashCardCollection->id)->get();
        return view('edit_flash_card_collection', [
            'flashCardCollection' => $flashCardCollection,
            'flashCards' => json_encode($flashCards),
            'language' => Auth::user()->selected_language,
        ]);
    }

    public function saveFlashCardCollection(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        if ($request->flashCardCollectionId != -1) {
            $flashCardCollection = FlashCardCollection::where('id', $request->flashCardCollectionId)->where('user_id', Auth::user()->id)->first();
        } else {
            $flashCardCollection = new FlashCardCollection();
            $flashCardCollection->user_id = Auth::user()->id;
        }
        
        $flashCardCollection->name = $request->name;
        $flashCardCollection->type = $request->type;
        $flashCardCollection->language = $selectedLanguage;
        $flashCardCollection->save();

        // save flash cards
        foreach (json_decode($request->flashCards) as $flashCard) {
            if ($flashCard->id == -1) {
                $currentFlashCard = new FlashCard();
                $currentFlashCard->level = 1;
                $currentFlashCard->reading = '';
                $currentFlashCard->last_reviewed = null;
            } else {
                $currentFlashCard = FlashCard::where('id', $flashCard->id)->where('user_id', Auth::user()->id)->first();
                if ($flashCard->deleted) {
                    $currentFlashCard->delete();
                }
            }

            if (!$flashCard->deleted) {
                $currentFlashCard->user_id = Auth::user()->id;
                $currentFlashCard->flash_card_collection_id = $flashCardCollection->id;
                $currentFlashCard->language = $flashCardCollection->language;
                $currentFlashCard->sentence_raw = $flashCard->sentence;
                $currentFlashCard->sentence_processed = '';
                $currentFlashCard->reading = $flashCard->reading;
                $currentFlashCard->level = $flashCard->level;
                $currentFlashCard->translation = $flashCard->translation;
                $currentFlashCard->save();
            }
        }
        
        shell_exec('./../tokenizer.py');
        return redirect('/flash-card-collections/' . $request->language);
    }
    
    public function deleteFlashCardCollection($flashCardCollectionId) {
        $flashCardCollection = FlashCardCollection::where('id', $flashCardCollectionId)->where('user_id', Auth::user()->id)->first();
        $language = $flashCardCollection->language;

        FlashCard::where('flash_card_collection_id', $flashCardCollection->id)->delete();
        $flashCardCollection->delete();

        return redirect('/flash-card-collections');
    }

    public function practiceFlashCards($flashCardCollectionId) {
        $flashCardCollection = FlashCardCollection::where('id', $flashCardCollectionId)->where('user_id', Auth::user()->id)->first();
        $selectedLanguage = Auth::user()->selected_language;
        
        $flashCardsQuery = FlashCard::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('flash_card_collection_id', $flashCardCollectionId)->get();

        $levelWeights = [
            10  => 1,
            9   => 10,
            8   => 20,
            7   => 50,
            6   => 75,
            5   => 100,
            4   => 125,
            3   => 150,
            2   => 200,
            1   => 300,
        ];
        
        // get total weight
        $totalWeight = 0;
        foreach($flashCardsQuery as $flashCard) {
            $totalWeight += $levelWeights[$flashCard->level];
        }

        // randomize with weight
        $flashCards = [];
        $flashCardIds = [];
        $flashCardSentences = [];
        while(count($flashCards) < 300 && count($flashCards) < count($flashCardsQuery)) {
            $randomWeight = mt_rand(1, $totalWeight);
            $currentWeight = 0;
            foreach($flashCardsQuery as $flashCard) {
                $currentWeight += $levelWeights[$flashCard->level];
                if ($currentWeight > $randomWeight) {
                    if (in_array($flashCard->id, $flashCardIds, true)) {
                        break;
                    } else {
                        array_push($flashCards, $flashCard);
                        array_push($flashCardIds, $flashCard->id);
                        break;
                    }
                }
            }
        }

        $uniqueWords = [];
        foreach($flashCards as $flashCard) {
            $flashCard->levelChanged = false;
            $words = json_decode($flashCard->sentence_processed);
            foreach($words as $word) {
                if (!in_array(mb_strtolower($word), $uniqueWords, true)) {
                    $uniqueWord = EncounteredWord::where('word', mb_strtolower($word))->where('language', $flashCard->language)->where('user_id', Auth::user()->id)->first();
                    if (!$uniqueWord) {
                        $uniqueWord = new \StdClass();
                        $uniqueWord->word = $word;
                        $uniqueWord->translation = '';
                        $uniqueWord->stage = 2;
                    }

                    array_push($uniqueWords, $uniqueWord);
                }
            }
        }

        return view('flash_card_practice', [
            'flashCards' => json_encode($flashCards),
            'uniqueWords' => json_encode($uniqueWords),
            'type' => $flashCardCollection->type,
        ]);
    }

    public function finishFlashCardPractice(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        $dailyAchivement = DailyAchivement::where('user_id', Auth::user()->id)->where('day', \date('Y-m-d'))->where('language', $selectedLanguage)->first();
        if (!$dailyAchivement) {
            $dailyAchivement = new DailyAchivement();
            $dailyAchivement->user_id = Auth::user()->id;
            $dailyAchivement->day = \date('Y-m-d');
            $dailyAchivement->read_words = 0;
            $dailyAchivement->reviewed_words = 0;
            $dailyAchivement->language = $selectedLanguage;
        }

        $dailyAchivement->read_words += $request->readWords;
        $dailyAchivement->save();

        foreach (json_decode($request->flashCards) as $currentFlashCard) {
            $flashCard = FlashCard::where('id', $currentFlashCard->id)->where('user_id', Auth::user()->id)->first();
            
            if ($flashCard->level !== $currentFlashCard->level) {
                $flashCard->last_reviewed = date("Y-m-d");
            }
            
            $flashCard->level = $currentFlashCard->level;
            $flashCard->save();
        }
    }
}

