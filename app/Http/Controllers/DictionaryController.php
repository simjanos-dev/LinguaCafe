<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use League\Csv\Reader;
use \Exception;
use \DeepL\Translator;
use App\Models\Dictionary;
use App\Models\ImportedDictionary;
use App\Models\VocabularyJmdict;
use App\Models\DeeplCache;
use App\Models\Setting;

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
                $dictionary->records = DB::table($dictionary->database_table_name)->selectRaw('count(*) as record_count')->get();
                $dictionary->records = $dictionary->records[0]->record_count;
            }
        }

        return json_encode($dictionaries);
    }

    /*
        This function updates a dictionary's color and enabled status.
    */
    public function updateDictionary(Request $request) {
        $dictionary = Dictionary::where('id', $request->post('id'))->first();

        if (!$dictionary) {
            return 'error';
        }

        $dictionary->enabled = $request->post('enabled');
        $dictionary->color = $request->post('color');
        $dictionary->save();

        return 'success';
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
            ->where('language', $language)
            ->get();

        // go through each dictionary and search in them
        foreach ($dictionaries as $dictionary) {
            $searchResultDictionary = new \stdClass();
            $searchResultDictionary->name = $dictionary->name;
            $searchResultDictionary->color = $dictionary->color;
            
            if ($dictionary->name == 'JMDict') {
                $searchResultDictionary->jmdictRecords = $this->searchJmDict($term);
            } else if (explode(' ', $dictionary->name)[0] == 'DeepL' && $dictionary->database_table_name == 'API') {
                $searchResultDictionary->records = $this->searchDeepl($language, $term);
            } else {
                $searchResultDictionary->records = $this->searchImportedDictionary($dictionary->database_table_name, $term);
            }

            $searchResultDictionaries[] = $searchResultDictionary;
        }

        return json_encode($searchResultDictionaries);
    }

    /*
        This function searches a dictionary imported by an admin/user.
    */
    private function searchImportedDictionary($dictionaryTable, $term) {
        $records = [];
        
        $dictionaryWords = ImportedDictionary
            ::fromTable($dictionaryTable)
            ->where('word', 'LIKE', $term . '%')
            ->orderByRaw('LENGTH(word)')
            ->limit(40)
            ->get();

        foreach ($dictionaryWords as $word) {
            $definitions = explode(';', $word->definitions);
            
            // check if there are duplicate records
            $duplicate = false;
            foreach ($records as $record) {
                if ($record->word == $word->word) {
                    $record->definitions[] = $word->definitions;
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
    private function searchDeepl($language, $term) {
        // retrieve api key from database
        $apiKeySetting = Setting::where('name', 'deeplApiKey')->first();
        $apiKey = json_decode($apiKeySetting->value);

        $hash = md5(mb_strtolower($term, 'UTF-8'));
        $languageCodes = config('langapp.deepl_language_codes');
        $records = [];

        // check if search term is already cached
        $cache = DeeplCache
            ::where('language', $language)
            ->where('hash', $hash)
            ->first();
        
        // make api call or retrieve definition from cache
        if ($cache) {
            $definitions = [$cache->definition];
        } else {
            // make api call
            $deepl = new \DeepL\Translator($apiKey);
            $result = $deepl->translateText($term, $languageCodes[$language], $languageCodes['english']);
            $definitions = [$result->text];

            // create cache
            $cache = new DeeplCache();
            $cache->language = $language;
            $cache->hash = $hash;
            $cache->definition = $result->text;
            $cache->save();
        }

        // return translation
        $record = new \stdClass();
        $record->word = $term;
        $record->definitions = $definitions;
        $records[] = $record;

        return $records;
    }

    /*
        This function searches JMDict, which requires
        custom search function.
    */
    private function searchJmDict($term) {
        $ids = [];
        // exact word matches
        $search = VocabularyJmdict::select('id')->whereRelation('words', 'word', 'like', $term)->get()->toArray();
        foreach ($search as $result) {
            if (!in_array($result, $ids, true)) {
                array_push($ids, $result);
            }
        }

        // exact reading matches
        $search = VocabularyJmdict::select('id')->whereRelation('readings', 'reading', 'like', $term)->get()->toArray();
        foreach ($search as $result) {
            if (!in_array($result, $ids, true)) {
                array_push($ids, $result);
            }
        }

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
        $skipHeader = boolval($request->post('skipHeader') === 'true');
        $delimiter = $request->post('delimiter') === null ? ' ' : $request->post('delimiter');
        $dictionaryName = $request->post('dictionaryName');
        $databaseTableName = $request->post('databaseName');
        $language = $request->post('language');
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
        $dictionary->language = $language;
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
