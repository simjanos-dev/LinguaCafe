<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use \DeepL\Translator;

// models
use App\Models\Dictionary;
use App\Models\Setting;
use App\Models\DeeplCache;

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

    public function isDeeplEnabled($language) {
        $deeplDictionary = Dictionary
            ::where('name', 'like', 'DeepL%')
            ->where('enabled', true)
            ->where('database_table_name','API')
            ->where('source_language', $language)
            ->first();

        if (!$deeplDictionary) {
            return false;
        } else {
            return true;
        }
    }

    public function getDeeplCharacterLimit() {
        // retrieve api key from database
        $deeplApiKeySetting = Setting::where('name', 'deeplApiKey')->first();
        $deeplApiKey = json_decode($deeplApiKeySetting->value);

        $deepl = new Translator($deeplApiKey);
        $usage = new \stdClass();
        $usage->limits = $deepl->getUsage();
        $usage->cachedDeeplTranslations = DeeplCache::select('id')->count('id');
        
        return $usage;
    }
}