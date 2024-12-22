<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Client\Pool;
use League\Csv\Reader;

// models
use App\Models\Dictionary;
use App\Models\Setting;
use App\Models\DeeplCache;
use App\Models\ImportedDictionary;
use App\Models\VocabularyJmdict;

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

    public function isAnyApiDictionaryEnabled($language): bool
    {
        $apiDictionary = Dictionary::query()
            ->where('enabled', true)
            ->where('database_table_name','API')
            ->where('source_language', $language)
            ->first();

        return boolval($apiDictionary);
    }

    public function getDeeplCharacterLimit() {
        // retrieve api key from database
        $deeplApiKeySetting = Setting::where('name', 'deeplApiKey')->first();
        $deeplApiKey = json_decode($deeplApiKeySetting->value);
        $deeplHost = Setting::where('name', 'deeplHost')->first()->decode();

        $response = HTTP::withHeaders([
            'Authorization' => 'DeepL-Auth-Key ' . $deeplApiKey,
            'Content-Type' => 'application/json',
        ])->get($deeplHost . '/usage');

        return [
            'limits' => json_decode($response->body()),
            'cachedDeeplTranslations' => DeeplCache::select('id')->count('id'),
        ];
    }

    public function searchDefinitions($language, $term) {
        $searchResultDictionaries = [];
        $dictionaries = Dictionary
            ::where('enabled', true)
            ->where('source_language', $language)
            ->get();

        // go through each dictionary and search in them
        foreach ($dictionaries as $dictionary) {
            $searchResultDictionary = new \stdClass();
            $searchResultDictionary->name = $dictionary->name;
            $searchResultDictionary->color = $dictionary->color;
            
            if ($dictionary->name === 'JMDict') {
                $searchResultDictionary->jmdictRecords = $this->searchJmDict($term);
            } else if($dictionary->type === 'supported' || $dictionary->type === 'custom_csv') {
                $searchResultDictionary->records = $this->searchImportedDictionary($dictionary->database_table_name, $term);
            } else {
                continue;
            }

            $searchResultDictionaries[] = $searchResultDictionary;
        }

        return $searchResultDictionaries;
    }

    // This function returns a list of exact matches from dictionaries for the hover popup vocabulary.
    public function searchDefinitionsForHoverVocabulary($language, $term) {
        $limit = 9;
        $searchResults = [];
        
        $dictionaries = Dictionary
            ::where('enabled', true)
            ->where('source_language', $language)
            ->get();

        // go through each dictionary and search in them
        foreach ($dictionaries as $dictionary) {
            $results = [];

            // make search based on dictionary type
            if ($dictionary->name == 'JMDict') {
                $searchRecords = $this->searchJmDict($term, true);
            } else if ($dictionary->database_table_name == 'API') {
                // skip dictionary if it's an api
                continue;
            } else {
                $searchRecords = $this->searchImportedDictionary($dictionary->database_table_name, $term, true);
            }

            // add definitions to the final search results
            foreach ($searchRecords as $searchRecord) {
                foreach ($searchRecord->definitions as $definition) {
                    // break loop if the search result limit is reached
                    if (count($searchResults) > $limit) {
                        break;
                    }
                    
                    // add definitions based on dictionary type
                    if ($dictionary->name == 'JMDict') {
                        foreach (explode(',', $definition) as $splitDefinition) {
                            $searchResults[] = $splitDefinition;
                        }
                    } else {
                        $searchResults[] = $definition;
                    }
                }
            }
        }

        /*
            Return the found definitions and the search term. Search
            term must be returned so the client knows which request it.
        */
        $result = new \stdClass();
        $result->term = $term;
        $result->definitions = array_values(array_unique($searchResults));

        return $result;
    }
    
    public function searchApiDictionaries(string $sourceLanguage, string $term): array
    {
        $definitions = [];
        $termHash = md5(mb_strtolower($term, 'UTF-8'));
        $apiDictionaries = Dictionary::query()
            ->whereIn('type', ['my_memory', 'deepl', 'libre_translate', 'custom_api'])
            ->where('enabled', true)
            ->where('source_language', $sourceLanguage)
            ->get();

        $responseAdditionalInfo = [];
        $responses = Http::pool(function (Pool $pool) use (
                $apiDictionaries, 
                $term,
                $termHash,
                &$definitions,
                &$responseAdditionalInfo,
        ) {
            foreach ($apiDictionaries as $dictionary) {

                // deepl
                if ($dictionary->type === 'deepl') {
                    // check if search term is already cached
                    $cache = DeeplCache::query()
                        ->where('source_language', $dictionary->source_language)
                        ->where('target_language', $dictionary->target_language)
                        ->where('hash', $termHash)
                        ->first();
                
                    if ($cache) {
                        $definitions[] = [
                            'dictionary' => $dictionary->name,
                            'dictionaryColor' => $dictionary->color,
                            'definitions' => [$cache->definition],
                            'term' => $cache->definition,
                        ];
                    } else {
                        $responseAdditionalInfo[] = [
                            'dictionary' => $dictionary->name,
                            'dictionaryColor' => $dictionary->color,
                            'dictionaryType' => $dictionary->type,
                            'targetLanguage' => $dictionary->target_language,
                            'term' => $term,
                        ];
                        
                        $this->buildDeeplRequest($pool, $dictionary, $term);
                    }
                }

                // my memory api
                if ($dictionary->type === 'my_memory') {
                    $responseAdditionalInfo[] = [
                        'dictionary' => $dictionary->name,
                        'dictionaryColor' => $dictionary->color,
                        'dictionaryType' => $dictionary->type,
                        'term' => $term,
                    ];

                    $this->buildMyMemoryRequest($pool, $dictionary, $term);
                }

                // libre translate
                if ($dictionary->type === 'libre_translate') {
                    $responseAdditionalInfo[] = [
                        'dictionary' => $dictionary->name,
                        'dictionaryColor' => $dictionary->color,
                        'dictionaryType' => $dictionary->type,
                        'term' => $term,
                    ];

                    $this->buildLibreTranslateRequest($pool, $dictionary, $term);
                }

                // custom api translate
                if ($dictionary->type === 'custom_api') {
                    $responseAdditionalInfo[] = [
                        'dictionary' => $dictionary->name,
                        'dictionaryColor' => $dictionary->color,
                        'dictionaryType' => $dictionary->type,
                        'term' => $term,
                    ];

                    $this->buildCustomApiTranslateRequest($pool, $dictionary, $term);
                }
            }
        });

        // format dictionary search responses to a unified format
        foreach($responses as $responseIndex => $response) {
            if (
                !$response instanceof \Illuminate\Http\Client\Response || 
                is_null($response->toPsrResponse()) || 
                !$response->ok()
            ) {
                $definitions[] = [
                    'definitions' => ['error'],
                    ...$responseAdditionalInfo[$responseIndex]
                ];

                continue;
            }

            $dictionaryType = $responseAdditionalInfo[$responseIndex]['dictionaryType'];
            unset($responseAdditionalInfo[$responseIndex]['dictionaryType']);

            if ($dictionaryType === 'deepl') {
                $definition = json_decode($response->body())->translations[0]->text;

                $deeplCache = new DeeplCache();
                $deeplCache->source_language = $sourceLanguage;
                $deeplCache->target_language = $responseAdditionalInfo[$responseIndex]['targetLanguage'];
                $deeplCache->hash = $termHash;
                $deeplCache->definition = $definition;
                $deeplCache->save();

                unset($responseAdditionalInfo[$responseIndex]['targetLanguage']);

                $definitions[] = [
                    'definitions' => [$definition],
                    ...$responseAdditionalInfo[$responseIndex]
                ];
            }
            
            
            if ($dictionaryType === 'my_memory') {
                $myMemoryDefinitions = [];
                $matches = json_decode($response->body());
                foreach($matches->matches as $match) {
                    if(!str_contains($match->segment, $responseAdditionalInfo[$responseIndex]['term'])) {
                        continue;
                    }

                    // updates term in case it found translation only for a similar search term
                    $responseAdditionalInfo[$responseIndex]['term'] = $match->segment;

                    $myMemoryDefinitions[] = $match->translation;
                }

                if (!count($myMemoryDefinitions)) {
                    continue;
                }

                $definitions[] = [
                    'definitions' => $myMemoryDefinitions,
                    ...$responseAdditionalInfo[$responseIndex]
                ];
            }

            if ($dictionaryType === 'libre_translate') {
                $response = json_decode($response->body());
                $definitions[] = [
                    'definitions' => [$response->translatedText],
                    ...$responseAdditionalInfo[$responseIndex]
                ];
            }

            if ($dictionaryType === 'custom_api') {
                $response = json_decode($response->body());
                $definitions[] = [
                    'definitions' => [$response->translatedText],
                    ...$responseAdditionalInfo[$responseIndex]
                ];
            }
        }

        return $definitions;
    }

    private function buildDeeplRequest(Pool $pool, Dictionary $dictionary, string $term): void
    {
        $deeplApiKey = Setting::where('name', 'deeplApiKey')->first()->decode();
        $deeplHost = Setting::where('name', 'deeplHost')->first()->decode();
        $deeplLanguageCodes = config('linguacafe.languages.deepl_language_codes');

        // DeepL does not support 'EN-US' for source language 
        // and 'PT-PT' for language, so I replace them
        $sourceLanguageCode = $deeplLanguageCodes[$dictionary->source_language];
        if ($sourceLanguageCode === 'EN-US') {
            $sourceLanguageCode = 'EN';
        }

        if ($sourceLanguageCode === 'PT-PT') {
            $sourceLanguageCode = 'PT';
        }

        $pool->withHeaders([
            'Authorization' => 'DeepL-Auth-Key ' . $deeplApiKey,
            'Content-Type' => 'application/json',
        ])->post($deeplHost . '/translate', [
            'text' => [$term],
            "source_lang" => $sourceLanguageCode,
            "target_lang" => $deeplLanguageCodes[$dictionary->target_language],
        ]);
    }

    private function buildMyMemoryRequest(Pool $pool, Dictionary $dictionary, string $term): void
    {
        $myMemoryLanguageCodes = config('linguacafe.languages.my_memory_supported_target_languages');
        $sourceLanguageCode = $myMemoryLanguageCodes[$dictionary->source_language];
        $targetLanguageCode = $myMemoryLanguageCodes[$dictionary->target_language];
        $pool->get('https://api.mymemory.translated.net/get?q=' . urlencode($term) . '!&langpair=' . $sourceLanguageCode . '|' . $targetLanguageCode);
    }

    private function buildLibreTranslateRequest(Pool $pool, Dictionary $dictionary, string $term): void
    {
        $myMemoryLanguageCodes = config('linguacafe.languages.libre_translate_language_codes');
        $sourceLanguageCode = $myMemoryLanguageCodes[$dictionary->source_language];
        $targetLanguageCode = $myMemoryLanguageCodes[$dictionary->target_language];
        $libreTranslateHost = json_decode(Setting::where('name', 'libreTranslateHost')->first()->value);
        $pool->post($libreTranslateHost, [
            'q' => $term,
            'source' => $sourceLanguageCode,
            'target' => $targetLanguageCode,
        ]);
    }

    private function buildCustomApiTranslateRequest(Pool $pool, Dictionary $dictionary, string $term): void
    {
        $pool->post($dictionary->api_host, [
            'q' => $term,
            'source' => strtolower($dictionary->source_language),
            'target' => strtolower($dictionary->target_language),
        ]);
    }
    
    public function searchInflections($term) {
        $ids = [];
        
        // exact word matches
        $search = VocabularyJmdict
            ::select('id')
            ->whereRelation('words', 'word', 'like', $term)
            ->get()
            ->toArray();

        foreach ($search as $result) {
            if (count($ids)) {
                break;
            }

            if (!in_array($result, $ids, true)) {
                array_push($ids, $result);
            }
        }

        // exact reading matches
        $search = VocabularyJmdict
            ::select('id')
            ->whereRelation('readings', 'reading', 'like', $term)
            ->get()
            ->toArray();

        foreach ($search as $result) {
            if (count($ids)) {
                break;
            }

            if (!in_array($result, $ids, true)) {
                array_push($ids, $result);
            }
        }

        $search = VocabularyJmdict
            ::select('conjugations')
            ->whereIn('id', $ids)
            ->first();
        
        if ($search) {
            return $search->conjugations;
        } else {
            return [];
        }
    }

    public function getDictionaryRecordCount($dictionaryTableName) {
        if (!Schema::hasTable($dictionaryTableName)) {
            throw new \Exception('Table not found.');
        }

        $recordCount = DB::table($dictionaryTableName)->count();
        
        return $recordCount;
    }

    public function deleteDictionary($dictionaryId) {
        $dictionary = Dictionary
            ::where('id', $dictionaryId)
            ->first();

        if (!$dictionary) {
            throw new \Exception('Dictionary does not exist.');
        }

        if($dictionary->database_table_name !== 'API') {
            Schema::drop($dictionary->database_table_name);
        }
        
        Dictionary::where('id', $dictionaryId)->delete();

        return true;
    }

    private function searchImportedDictionary($dictionaryTable, $term, $strict = false) {
        $records = [];
        
        // if strict is true, only return exact matches
        if ($strict) {
            $dictionaryWords = ImportedDictionary
                ::fromTable($dictionaryTable)
                ->where('word', $term)
                ->limit(40)
                ->get();
        } else {
            $dictionaryWords = ImportedDictionary
                ::fromTable($dictionaryTable)
                ->where('word', 'LIKE', $term . '%')
                ->orderByRaw('LENGTH(word)')
                ->limit(40)
                ->get();
        }

        foreach ($dictionaryWords as $word) {
            $definitions = explode(';', $word->definitions);
            
            if (strlen($word->definitions) === 0) {
                continue;
            }

            // check if there are duplicate records
            $duplicate = false;
            foreach ($records as $record) {
                if ($record->word == $word->word) {
                    if (!in_array($word->definitions, $record->definitions, true)) {
                        $record->definitions[] = $word->definitions;
                    }
                    
                    $duplicate = true;
                }
            }

            if (!$duplicate) {
                $record = new \stdClass();
                $record->word = $word->word;
                $record->definitions = $definitions;
                $records[] = $record;
            }
        }

        return $records;
    }

    private function searchJmDict($term, $strict = false) {
        $ids = [];
        // exact word matches
        $search = VocabularyJmdict::select('id')->whereRelation('words', 'word', $term)->get()->toArray();
        foreach ($search as $result) {
            if (!in_array($result, $ids, true)) {
                array_push($ids, $result);
            }
        }

        // exact reading matches
        $search = VocabularyJmdict::select('id')->whereRelation('readings', 'reading', $term)->get()->toArray();
        foreach ($search as $result) {
            if (!in_array($result, $ids, true)) {
                array_push($ids, $result);
            }
        }

        // if strict is true, do not return partial matches
        if (!$strict) {
            // partial word matches, max 10
            $search = VocabularyJmdict::select('id')->whereRelation('words', 'word', 'like', $term . '%')->get()->toArray();
            foreach ($search as $result) {
                if (!in_array($result, $ids, true) && count($ids) < 10) {
                    array_push($ids, $result);
                }
            }

            // partial reading matches, max 10
            $search = VocabularyJmdict::select('id')->whereRelation('readings', 'reading', 'like', $term . '%')->get()->toArray();
            foreach ($search as $result) {
                if (!in_array($result, $ids, true) && count($ids) < 10) {
                    array_push($ids, $result);
                }
            }
        }

        $search = VocabularyJmdict::with('words:word,id,dict_jp_jmdict_id')->with('readings:reading,word_restrictions,id,dict_jp_jmdict_id')->whereIn('id', $ids)->get();
        
        $translations = [];
        foreach ($search as $result) {
            $translation = new \stdClass();
            $translation->words = [];
            $translation->definitions = [];
            $translation->conjugations = $result->conjugations == '' ? [] : json_decode($result->conjugations);

            $dictionaryDefinitions = json_decode($result->translations);
            foreach ($dictionaryDefinitions as $definition) {
                if (count($definition->restrictions)) {
                    array_push($translation->definitions, '(' . implode(', ', $definition->restrictions) . ') ' . $definition->definition);
                } else {
                    array_push($translation->definitions, $definition->definition);
                }
            }

            // make each word form a result
            foreach ($result->words as $word) {
                // get all possible readings for each word forms
                foreach ($result->readings as $reading) {
                    array_push($translation->words, $word->word . ' (' . $reading->reading . ')');
                }
            }

            array_push($translations, $translation);
        }

        return $translations;
    }
}