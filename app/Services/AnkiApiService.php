<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Setting;

class AnkiApiService
{
    protected $ankiHost = '';
    protected $updateCards = false;

    public function __construct()
    {
        $ankiHostSetting = Setting::where('name', 'ankiConnectHost')->first();
        $ankiUpdateCardsSetting = Setting::where('name', 'ankiUpdateCards')->first();
        $this->ankiHost = json_decode($ankiHostSetting->value);
        $this->updateCards = json_decode($ankiUpdateCardsSetting->value);
    }

    /*
        Adds or updates a card in anki.
    */
    public function addWord($language, $word, $reading, $translation, $exampleSentence) {
        // try to insert word into anki with api
        $firstInsertResult = $this->insertNote($language, $word, $reading, $translation, $exampleSentence);

        // if the insert throws a no deck found error, then
        // create a model, a deck and insert the word again.
        if ($firstInsertResult == 'Missing model or deck.') {
            $createDeckResult = $this->createAnkiDeckAndModel($language);
            if ($createDeckResult !== 'success') {
                return $createDeckResult;
            }
            
            $secondInsertResult = $this->insertNote($language, $word, $reading, $translation, $exampleSentence);
            
            // can be success
            return $secondInsertResult;
        } else {
            // can be 'success' as well
            return $firstInsertResult;
        }
    }

    /*
        Sends an api call to anki to create new model and deck.
    */
    public function createAnkiDeckAndModel($language) {
        $createModelData = '{
            "action": "createModel",
            "version": 6,
            "params": {
                "css": ".card {display: block;margin-left: auto;margin-right: auto;font-family: arial;font-size: 20px;text-align: center;color: black;max-width: 600px;border: 1px solid;border-radius: 8px;padding: 16px;}",
                "modelName": "LinguaCafe' . ucfirst($language) . '",
                "inOrderFields": ["word", "reading", "translation", "example_sentence"],
                "cardTemplates": [
                    {
                        "Name": "LinguaCafe' . ucfirst($language) . '",
                        "Front": "<ruby>{{word}}<rt>{{reading}}</rt></ruby><hr>{{example_sentence}}",
                        "Back": "<ruby>{{word}}<rt>{{reading}}</rt></ruby><hr>{{example_sentence}}<hr>{{translation}}"
                    }
                ]
            }
        }';
        
        $createDeckData = '{
            "action": "createDeck",
            "version": 6,
            "params": {
                "deck": "LinguaCafe::' . ucfirst($language) . '"
            }
        }';

        try {
            // create model anki api call
            $createModelResult = Http
                ::withBody($createModelData, 'application/json')
                ->post($this->ankiHost);
            
            $createModelResult = json_decode($createModelResult);
            if ($createModelResult->error !== null) {
                if ($createModelResult->error !== 'Model name already exists') {
                    throw new \Exception('Create model error: ' . $createModelResult->error);
                }
            }

            // create deck anki api call
            $createDeckResult = Http
                ::withBody($createDeckData, 'application/json')
                ->post($this->ankiHost);
            $createDeckResult = json_decode($createDeckResult);

            if ($createDeckResult->error !== null) {
                throw new \Exception('Create deck error:' . $createDeckResult->error);
            }
        } catch (\Exception $e) {
            return 'Error:' . $e->getMessage();
        }
        
        return 'success';
    }

    /*
        Sends an api call to anki to insert a new note. 
        It will update the not if it already exists.
    */
    private function insertNote($language, $word, $reading, $translation, $exampleSentence) {
        $insertNoteApiData = '{
            "version": 6,
            "action": "addNote",
            "params": {
                "note": {
                    "deckName": "LinguaCafe::' . ucfirst($language) . '",
                    "modelName": "LinguaCafe' . ucfirst($language) . '",
                    "fields": {
                        "word": "' . $word . '",
                        "reading": "' . $reading . '",
                        "translation": "' . $translation . '",
                        "example_sentence": "' . $exampleSentence . '"
                    },
                    "options": {
                        "allowDuplicate": false,
                        "duplicateScope": "deck",
                        "duplicateScopeOptions": {
                            "deckName": "LinguaCafe::' . ucfirst($language) . '",
                            "checkChildren": false,
                            "checkAllModels": false
                        }
                    }
                }
            }
        }';

        try {
            $insertNoteResult = Http
                ::withBody($insertNoteApiData, 'application/json')
                ->post($this->ankiHost);

            $insertNoteResult = json_decode($insertNoteResult);
            
            // missing deck error
            if (is_string($insertNoteResult->error) && str_contains($insertNoteResult->error, 'was not found: LinguaCafe')) {
                return 'Missing model or deck.';
            }
            
            // update note if it's a duplicate
            if (is_string($insertNoteResult->error) && $insertNoteResult->error === 'cannot create note because it is a duplicate') {
                if (!$this->updateCards) {
                    return 'This card already exists in anki, and update cards setting is turned off.';
                }

                $updateNoteResponse = $this->updateNote($language, $word, $reading, $translation, $exampleSentence);
                return $updateNoteResponse;
            }

            // any other error
            if (is_string($insertNoteResult->error) && $insertNoteResult->error !== 'cannot create note because it is a duplicate') {
                return 'An error has occurred:' . $insertNoteResult->error;
            }
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'cURL error 7: Failed to connect')) {
                return 'Failed to connect to anki.';
            } else {
                return 'An error has occurred:' . $e->getMessage();
            }
        }

        return 'success';
    }

    /*
        Sends an api call to anki to update a note.
    */
    private function updateNote($language, $word, $reading, $translation, $exampleSentence) {
        $findNoteApiData = '{
            "action": "findNotes",
            "version": 6,
            "params": {
                "query": "deck:LinguaCafe::' . ucfirst($language) . ' word:' . $word . '"
            }
        }';

        try {
            $findNoteResult = Http
                ::withBody($findNoteApiData, 'application/json')
                ->post($this->ankiHost);

            $findNoteResult = json_decode($findNoteResult);
            
            // error while requesting duplicate note id
            if (is_string($findNoteResult->error)) {
                return 'Error retrieving duplicate note:' . $findNoteResult->error;
            }

            // empty response when requesting duplicate note id
            if (!count($findNoteResult->result)) {
                return 'Request retrieving duplicate note returned empty.';
            }
        } catch (\Exception $e) {
            return 'Error retrieving duplicate note id:' . $e->getMessage();
        }

        // create request for ntoe update
        $duplicateNoteId = $findNoteResult->result[0];
        $updateNoteApiData = '{
            "action": "updateNoteFields",
            "version": 6,
            "params": {
                "note": {
                    "id": ' . $duplicateNoteId . ',
                    "fields": {
                        "reading": "' . $reading . '",
                        "translation": "' . $translation . '",
                        "example_sentence": "' . $exampleSentence . '"
                    }
                }
            }
        }';

        try {
            $updateNoteResult = Http
                ::withBody($updateNoteApiData, 'application/json')
                ->post($this->ankiHost);

            $updateNoteResult = json_decode($updateNoteResult);
            
            // error while updating duplicate note
            if (is_string($updateNoteResult->error)) {
                return 'Error updating note:' . $updateNoteResult->error;
            }
        } catch (\Exception $e) {
            return 'Error updating note:' . $e->getMessage();
        }

        return 'update success';
    }
}