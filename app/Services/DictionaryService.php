<?php

namespace App\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Client\Pool;
use \DeepL\Translator;
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
            ->whereIn('type', ['my_memory', 'deepl', 'libre_translate'])
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
                            'definitions' => $cache->definition,
                            'term' => $cache->definition,
                        ];
                    } else {
                        $responseAdditionalInfo[] = [
                            'dictionary' => $dictionary->name,
                            'dictionaryColor' => $dictionary->color,
                            'dictionaryType' => $dictionary->type,
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
            }
        });

        // format dictionary search responses to a unified format
        foreach($responses as $responseIndex => $response) {
            if (!$response->ok()) {
                $definitions[] = [
                    'definitions' => ['error'],
                    ...$responseAdditionalInfo[$responseIndex]
                ];

                continue;
            }

            $dictionaryType = $responseAdditionalInfo[$responseIndex]['dictionaryType'];
            unset($responseAdditionalInfo[$responseIndex]['dictionaryType']);
            if ($dictionaryType === 'deepl') {
                $definitions[] = [
                    'definitions' => [json_decode($response->body())->translations[0]->text],
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
        $pool->post('http://libretranslate:5000/translate', [
            'q' => $term,
            'source' => $sourceLanguageCode,
            'target' => $targetLanguageCode,
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

    public function testDictionaryCsvFile($file, $delimiter, $skipHeader) {
        $returnData = new \stdClass();
        $returnData->status = 'success';
        $returnData->sample = [];
        $returnData->recordCount = 0;

        // move file to a temp folder
        $fileName = bin2hex(openssl_random_pseudo_bytes(30)) . '.csv';
        $file->move(storage_path('app/temp'), $fileName);
        
        // try to read file and collect sample rows
        try {
            $csv = Reader::createFromPath(storage_path('app/temp') . '/' . $fileName, 'r');
            $csv->setDelimiter($delimiter);
            $records = $csv->getRecords();
            foreach ($records as $index => $record) {
                // ignore header
                if ($skipHeader && !$index) {
                    continue;
                }

                // check if both columns exist
                if (!isset($record[0]) || !isset($record[1])) {
                    throw new \Exception('Missing data.');
                }

                $sampleData = new \stdClass();
                $sampleData->word = mb_strtolower($record[0], 'UTF-8');
                $sampleData->translation = $record[1];

                // this loop runs through the whole file to test for errors
                if (($skipHeader && $index <= 3) || (!$skipHeader && $index < 3)) {
                    $returnData->sample[] = $sampleData;
                }

                $returnData->recordCount ++;
            }
        } catch (\Exception $exception) {
            $returnData->sample = [];
            $returnData->status = 'error';
        }

        File::delete(storage_path('app/temp') . '/' . $fileName);

        return $returnData;
    }

    public function importDictionaryCsvFile($file, $skipHeader, $delimiter, $dictionaryName, $databaseTableName, $sourceLanguage, $targetLanguage, $color) {
        set_time_limit(2400);
        
        if(!preg_match('/^[a-z0-9_]+$/', $databaseTableName)) {
            throw new \Exception('Database name can only contain lowercase letters, numbers and underscore!');
        }

        if(mb_strlen($dictionaryName) > 16) {
            throw new \Exception('Dictionary name can only contain up to 16 characters!');
        }

        if(mb_strlen($databaseTableName) > 40) {
            throw new \Exception('Database name can only contain up to 40 characters!');
        }

        // check if table exists
        if (Schema::hasTable($databaseTableName)) {
            throw new \Exception('Database table name already exists');
        }
        
        // create table
        Schema::create($databaseTableName, function (Blueprint $table) {
            $table->id();
            $table->string('word', 256)->collation('utf8mb4_bin')->index();
            $table->string('definitions', 2048)->collation('utf8mb4_bin');
            $table->timestamps();
        });

        $dictionary = new Dictionary();
        $dictionary->name = $dictionaryName;
        $dictionary->type = 'custom_csv';
        $dictionary->database_table_name = $databaseTableName;
        $dictionary->source_language = $sourceLanguage;
        $dictionary->target_language = $targetLanguage;
        $dictionary->color = $color;
        $dictionary->enabled = true;
        $dictionary->save();

        // move file to a temp folder
        $fileName = bin2hex(openssl_random_pseudo_bytes(30)) . '.csv';
        $file->move(storage_path('app/temp'), $fileName);
        
        // try to read file and collect sample rows
        DB::beginTransaction();
        $csv = Reader::createFromPath(storage_path('app/temp') . '/' . $fileName, 'r');
        $csv->setDelimiter($delimiter);
        $records = $csv->getRecords();
        foreach ($records as $index => $record) {
            // ignore header
            if ($skipHeader && !$index) {
                continue;
            }

            // check if both columns exist
            if (!isset($record[0]) || !isset($record[1])) {
                throw new \Exception('Missing data.');
            }

            if (mb_strlen($record[0]) > 255 || mb_strlen($record[1]) > 2047) {
                continue;
            }
            
            DB::table($databaseTableName)->insert([
                'word' => mb_strtolower($record[0], 'UTF-8'),
                'definitions' => $record[1]
            ]);
        }

        DB::commit();
        File::delete(storage_path('app/temp') . '/' . $fileName);
        
        return true;
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