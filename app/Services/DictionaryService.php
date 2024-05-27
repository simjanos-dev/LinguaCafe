<?php

namespace App\Services;

use App\Models\Dictionary;
use Illuminate\Support\Facades\DB;

class DictionaryService {

    public function __construct() {
    }

    public function getDictionaries() {
        $dictionaries = Dictionary::get();

        foreach ($dictionaries as $dictionary) {
            if ($dictionary->database_table_name == 'API') {
                $dictionary->records = '-';
            } else {
                $dictionary->records = DB
                    ::table($dictionary->database_table_name)
                    ->selectRaw('count(*) as record_count')
                    ->get();

                $dictionary->records = $dictionary->records[0]->record_count;
            }
        }

        return $dictionaries;
    }

    public function getDictionary($dictionaryId) {
        $dictionary = Dictionary
            ::where('id', $dictionaryId)
            ->first();

        if ($dictionary->database_table_name == 'API') {
            $dictionary->records = '-';
        } else {
            $dictionary->records = DB
                ::table($dictionary->database_table_name)
                ->selectRaw('count(*) as record_count')
                ->get();

            $dictionary->records = $dictionary->records[0]->record_count;
        }

        return $dictionary;
    }

    public function updateDictionary($dictionaryId, $dictionaryData) {
        $dictionary = Dictionary
            ::where('id', $dictionaryId)
            ->first();

        if (!$dictionary) {
            throw new \Exception('Dictionary not found.');
        }

        // update dictionary data
        foreach ($dictionaryData as $field => $value) {
            $dictionary->$field = $value;
        }

        $dictionary->save();

        return true;
    }
}