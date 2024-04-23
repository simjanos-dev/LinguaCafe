<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use League\Csv\Reader;
use \DeepL\Translator;
use App\Models\Dictionary;
use App\Models\ImportedDictionary;
use App\Models\VocabularyJmdict;
use App\Models\DeeplCache;
use App\Models\Setting;
use App\Services\DictionaryImportService;


class DictionaryController extends Controller
{
    /*
        Returns a list of dictionaries.
    */
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

        return json_encode($dictionaries);
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

        return json_encode($dictionary);
    }

    public function updateDictionary(Request $request) {
        $dictionary = Dictionary
            ::where('id', $request->post('id'))
            ->first();

        if (!$dictionary) {
            return 'error';
        }

        if (isset($request->name)) {
            $dictionary->name = $request->post('name');
        }

        if (isset($request->source_language)) {
            $dictionary->source_language = $request->post('source_language');
        }

        if (isset($request->target_language)) {
            $dictionary->target_language = $request->post('target_language');
        }

        if (isset($request->color)) {
            $dictionary->color = $request->post('color');
        }
        
        if (isset($request->enabled)) {
            $dictionary->enabled = $request->post('enabled');
        }

        $dictionary->save();

        return 'success';
    }

    /*
        Returns if any DeepL dictionary is enabled for the current user and selected language.
    */
    public function isDeeplEnabled() {
        $userId = Auth::user()->id;
        $language = Auth::user()->selected_language;

        $deeplDictionary = Dictionary
            ::where('name', 'like', 'DeepL%')
            ->where('enabled', true)
            ->where('database_table_name','API')
            ->where('source_language', $language)
            ->first();

        if (!$deeplDictionary) {
            return response()->json(false, 200);
        }
        
        return response()->json(true, 200);
    }

    /*
        Returns an object with DeepL's character limit.
    */
    public function getDeeplCharacterLimit() {
        $usage = 'error';

        // retrieve api key from database
        $apiKeySetting = Setting::where('name', 'deeplApiKey')->first();
        $apiKey = json_decode($apiKeySetting->value);

        // retrieve deepl usage
        try {
            $deepl = new Translator($apiKey);
            $usage = new \stdClass();
            $usage->limits = $deepl->getUsage();
            $usage->cachedDeeplTranslations = DeeplCache::select('id')->count('id');
        } catch (\Exception $e) {
            return 'error';
        }

        
        return json_encode($usage);
    }

    /*
        This function is called by the client, it searches through all
        dictionaries available and enabled (imported, JMDict, DeepL etc.). 
    */
    public function searchDefinitions(Request $request) {
        $language = $request->post('language');
        $term = $request->post('term');
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
            
            if ($dictionary->name == 'JMDict') {
                $searchResultDictionary->jmdictRecords = $this->searchJmDict($term);
            } else if (explode(' ', $dictionary->name)[0] == 'DeepL' && $dictionary->database_table_name == 'API') {
                continue;
            } else {
                $searchResultDictionary->records = $this->searchImportedDictionary($dictionary->database_table_name, $term);
            }

            $searchResultDictionaries[] = $searchResultDictionary;
        }

        return json_encode($searchResultDictionaries);
    }

    /*
        This function returns a list of exact matches from dictionaries for the hover popup vocabulary.
    */
    public function searchDefinitionsForHoverVocabulary(Request $request) {
        $language = $request->post('language');
        $term = $request->post('term');
        $limit = 15;
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
        return json_encode($result);
    }

    /*
        This function searches a dictionary imported by an admin/user.
    */
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

    /*
        This function sends an API request to DeepL, and returns
        it in a format that can be returned for the client.
    */
    public function searchDeepl(Request $request) {
        $language = $request->post('language');
        $term = $request->post('term');

        $deeplDictionary = Dictionary
            ::where('name', 'like', 'DeepL%')
            ->where('enabled', true)
            ->where('database_table_name','API')
            ->where('source_language', $language)
            ->first();

        if (!$deeplDictionary) {
            return response()->json([
                'message' => 'DeepL dictionary is disabled.'
            ], 500);
        }

        // retrieve api key from database
        $apiKeySetting = Setting::where('name', 'deeplApiKey')->first();
        $apiKey = json_decode($apiKeySetting->value);

        $hash = md5(mb_strtolower($term, 'UTF-8'));
        $languageCodes = config('linguacafe.languages.deepl_language_codes');

        // check if search term is already cached
        $cache = DeeplCache
            ::where('source_language', $language)
            ->where('target_language', $deeplDictionary->target_language)
            ->where('hash', $hash)
            ->first();
        
        // make api call or retrieve definition from cache
        if ($cache) {
            $definition = $cache->definition;
        } else {
            // make api call
            $deepl = new \DeepL\Translator($apiKey);

            // DeepL does not support 'EN-US' for source language 
            // and 'PT-PT' for language, so I replace them
            $sourceLanguage = $languageCodes[$language];
            if ($sourceLanguage === 'EN-US') {
                $sourceLanguage = 'EN';
            }

            if ($sourceLanguage === 'PT-PT') {
                $sourceLanguage = 'PT';
            }

            $result = $deepl->translateText($term, $sourceLanguage, $languageCodes[$deeplDictionary->target_language]);
            $definition = $result->text;

            // create cache
            $cache = new DeeplCache();
            $cache->source_language = $language;
            $cache->target_language = $deeplDictionary->target_language;
            $cache->hash = $hash;
            $cache->definition = $result->text;
            $cache->save();
        }

        // return translation
        $result = new \stdClass();
        $result->term = $term;
        $result->definition = $definition;

        return json_encode($result);
    }

    /*
        This function searches JMDict, which requires
        custom search function.
    */
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

    /* 
        This function searches inflections from JMDict. 
    */
    public function searchInflections(Request $request) {
        $dictionary = $request->dictionary;
        $term = $request->term;

        $ids = [];
        // exact word matches
        $search = VocabularyJmdict::select('id')->whereRelation('words', 'word', 'like', $term)->get()->toArray();
        foreach ($search as $result) {
            if (count($ids)) {
                break;
            }

            if (!in_array($result, $ids, true)) {
                array_push($ids, $result);
            }
        }

        // exact reading matches
        $search = VocabularyJmdict::select('id')->whereRelation('readings', 'reading', 'like', $term)->get()->toArray();
        foreach ($search as $result) {
            if (count($ids)) {
                break;
            }

            if (!in_array($result, $ids, true)) {
                array_push($ids, $result);
            }
        }

        $search = VocabularyJmdict::select('conjugations')->whereIn('id', $ids)->first();
        
        if ($search) {
            return json_encode($search->conjugations);   
        } else {
            return json_encode([]);
        }
    }

    /*
        This function tests a .csv file, and returns a sample of the data.
        This makes it faster to test a file and notice any problems before
        the user actually imports a large file.
    */
    public function testDictionaryCsvFile(Request $request) {
        $skipHeader = boolval($request->post('skipHeader') === 'true');
        $delimiter = $request->post('delimiter') === null ? ' ' : $request->post('delimiter');

        $returnData = new \stdClass();
        $returnData->status = 'success';
        $returnData->sample = [];
        $returnData->recordCount = 0;

        // move file to a temp folder
        $file = $request->file('dictionary');
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
        return json_encode($returnData);
    }

    /*
        Imports a csv file into a custom dictionary database table.
    */
    public function importDictionaryCsvFile(Request $request) {
        set_time_limit(2400);
        $skipHeader = boolval($request->post('skipHeader') === 'true');
        $delimiter = $request->post('delimiter') === null ? ' ' : $request->post('delimiter');
        $dictionaryName = $request->post('dictionaryName');
        $databaseTableName = $request->post('databaseName');
        $sourceLanguage = $request->post('sourceLanguage');
        $targetLanguage = $request->post('targetLanguage');
        $color = $request->post('color');

        if(!preg_match('/^[a-z0-9_]+$/', $databaseTableName)) {
            return 'Database name can only contain lowercase letters, numbers and underscore!';
        }

        if(mb_strlen($dictionaryName) > 16) {
            return 'Dictionary name can only contain up to 16 characters!';
        }

        if(mb_strlen($databaseTableName) > 40) {
            return 'Database name can only contain up to 40 characters!';
        }

        // check if table exists
        if (Schema::hasTable($databaseTableName)) {
            return 'Database table name already exists';
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
        $dictionary->database_table_name = $databaseTableName;
        $dictionary->source_language = $sourceLanguage;
        $dictionary->target_language = $targetLanguage;
        $dictionary->color = $color;
        $dictionary->enabled = true;
        $dictionary->save();

        // move file to a temp folder
        $file = $request->file('dictionary');
        $fileName = bin2hex(openssl_random_pseudo_bytes(30)) . '.csv';
        $file->move(storage_path('app/temp'), $fileName);
        
        // try to read file and collect sample rows
        try {
            DB::beginTransaction();
            $csv = Reader::createFromPath(storage_path('app/temp') . '/' . $fileName, 'r');
            $csv->setDelimiter($delimiter);
            $records = $csv->getRecords();
            $uniqueWords = [];
            foreach ($records as $index => $record) {
                // ignore header
                if ($skipHeader && !$index) {
                    continue;
                }

                // check if both columns exist
                if (!isset($record[0]) || !isset($record[1])) {
                    throw new \Exception('Missing data.');
                }

                DB::table($databaseTableName)->insert([
                    'word' => mb_strtolower($record[0], 'UTF-8'),
                    'definitions' => $record[1]
                ]);
            }

            DB::commit();
        } catch (\Exception $exception) {
            File::delete(storage_path('app/temp') . '/' . $fileName);
            return 'error';
        }

        File::delete(storage_path('app/temp') . '/' . $fileName);
        return 'success';
    }

    /*
        Scans the /storage/app/dictionaries folder, 
        and returns a list of importable dictionaries.
    */
    public function getImportableDictionaryList() {
        
        $dictCcLanguageCodes = config('linguacafe.languages.dict_cc_language_codes');
        $databaseLanguageCodes = config('linguacafe.languages.database_name_language_codes');
        $supportedSourceLanguages = config('linguacafe.languages.supported_languages');
        
        $dictionaryImportService = new DictionaryImportService();
        $dictionariesFound = $dictionaryImportService->getImportableDictionaryList($supportedSourceLanguages, $dictCcLanguageCodes, $databaseLanguageCodes);
        
        return json_encode($dictionariesFound);
    }

    public function importSupportedDictionary(Request $request) {
        set_time_limit(2400);
        $dictionaryName = $request->post('dictionaryName');
        $dictionaryFileName = $request->post('dictionaryFileName');
        $dictionarySourceLanguage = $request->post('dictionarySourceLanguage');
        $dictionaryTargetLanguage = $request->post('dictionaryTargetLanguage');
        $dictionaryDatabaseName = $request->post('dictionaryDatabaseName');

        // import jmdict files
        if ($dictionaryName == 'JMDict') {
            try {
                $dictionaryImportService = new DictionaryImportService();
                $dictionaryImportService->jmdictImport();
                $dictionaryImportService->kanjiImport();
                $dictionaryImportService->kanjiRadicalImport();
            } catch (\Throwable $t) {
                return 'error';
            } catch (\Exception $e) {
                return 'error';
            }

            return 'success';
        }

        // import cc cedict or HanDeDict file
        if ($dictionaryName == 'cc-cedict' || $dictionaryName == 'HanDeDict') {
            try {
                $dictionaryImportService = new DictionaryImportService();
                $dictionaryImportService->importCeDictOrHanDeDict($dictionaryName, $dictionaryTargetLanguage, $dictionaryDatabaseName, $dictionaryFileName);
            } catch (\Throwable $t) {
                return 'error';
            } catch (\Exception $e) {
                return 'error';
            }

            return 'success';
        }

        // import kengdic file
        if ($dictionaryName == 'kengdic') {
            try {
                $dictionaryImportService = new DictionaryImportService();
                $dictionaryImportService->importKengdic($dictionaryName, $dictionaryDatabaseName, $dictionaryFileName);
            } catch (\Throwable $t) {
                return 'error';
            } catch (\Exception $e) {
                return 'error';
            }

            return 'success';
        }

        // import eurfa files
        if ($dictionaryName == 'eurfa') {
            try {
                $dictionaryImportService = new DictionaryImportService();
                $dictionaryImportService->importEurfa($dictionaryName, $dictionaryDatabaseName, $dictionaryFileName);
            } catch (\Throwable $t) {
                return $t->getMessage();
                return 'error';
            } catch (\Exception $e) {
                return $e->getMessage();
                return 'error';
            }

            return 'success';
        }
        

        // import dict cc files
        if (str_contains($dictionaryName, 'dictcc')) {
            try {
                $dictionaryImportService = new DictionaryImportService();
                $dictionaryImportService->importDictCc(
                    $dictionaryName, 
                    $dictionarySourceLanguage, 
                    $dictionaryTargetLanguage,
                    $dictionaryFileName, 
                    $dictionaryDatabaseName
                );
            } catch (\Throwable $t) {
                DB::
                    table('dictionaries')
                    ->where('database_table_name', $dictionaryDatabaseName)
                    ->delete();

                Schema::dropIfExists($dictionaryDatabaseName);
                return 'error';
            } catch (\Exception $e) {
                DB::
                    table('dictionaries')
                    ->where('database_table_name', $dictionaryDatabaseName)
                    ->delete();

                Schema::dropIfExists($dictionaryDatabaseName);
                return 'error';
            }

            return 'success';
        }

        // import wiktionary files
        if (str_contains($dictionaryName, 'wiktionary')) {
            try {
                $dictionaryImportService = new DictionaryImportService();
                $dictionaryImportService->importWiktionary(
                    $dictionaryName, 
                    $dictionarySourceLanguage, 
                    $dictionaryFileName, 
                    $dictionaryDatabaseName
                );
            } catch (\Throwable $t) {
                DB::
                    table('dictionaries')
                    ->where('database_table_name', $dictionaryDatabaseName)
                    ->delete();

                Schema::dropIfExists($dictionaryDatabaseName);
                return 'error';
            } catch (\Exception $e) {
                DB::
                    table('dictionaries')
                    ->where('database_table_name', $dictionaryDatabaseName)
                    ->delete();
                    
                Schema::dropIfExists($dictionaryDatabaseName);
                return 'error';
            }
            return 'success';
        }
    }

    /*
        Returns the number of records in a dictionary database table.
        It is used to display import progress bar.
    */
    public function getDictionaryRecordCount($dictionaryTableName) {
        if (!Schema::hasTable($dictionaryTableName)) {
            return 0;
        }

        $recordCount = DB::table($dictionaryTableName)->count();
        return $recordCount;
    }

    public function deleteDictionary($dictionaryTableName) {
        try {
            Schema::drop($dictionaryTableName);
            Dictionary::where('database_table_name', $dictionaryTableName)->delete();
        } catch (\Exception $exception) {
            return 'error';
        }

        return 'success';
    }
}
