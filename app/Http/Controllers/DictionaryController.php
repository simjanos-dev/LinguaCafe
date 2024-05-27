<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use League\Csv\Reader;
use App\Models\Dictionary;

// services
use App\Services\DictionaryService;
use App\Services\DictionaryImportService;

// request classes
use App\Http\Requests\Dictionaries\GetDictionaryFileInformationRequest;
use App\Http\Requests\Dictionaries\CreateDeeplDictionaryRequest;
use App\Http\Requests\Dictionaries\GetDictionaryRequest;
use App\Http\Requests\Dictionaries\UpdateDictionaryRequest;
use App\Http\Requests\Dictionaries\SearchDefinitionsRequest;
use App\Http\Requests\Dictionaries\SearchDefinitionsForHoverVocabularyRequest;
use App\Http\Requests\Dictionaries\SearchDeeplRequest;
use App\Http\Requests\Dictionaries\SearchInflectionsRequest;
use App\Http\Requests\Dictionaries\TestDictionaryCsvFileRequest;

class DictionaryController extends Controller
{
    private $dictionaryService;
    private $dictionaryImportService;
    
    public function __construct(DictionaryService $dictionaryService, DictionaryImportService $dictionaryImportService) {
        $this->dictionaryService = $dictionaryService;
        $this->dictionaryImportService = $dictionaryImportService;
    }

    /*
        Returns a list of dictionaries.
    */
    public function getDictionaries() {

        try {
            $dictionaries = $this->dictionaryService->getDictionaries();
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($dictionaries, 200);
    }

    public function getDictionary($dictionaryId, GetDictionaryRequest $request) {
        
        try {
            $dictionary = $this->dictionaryService->getDictionary($dictionaryId);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($dictionary, 200);
    }

    public function updateDictionary(UpdateDictionaryRequest $request) {
        $dictionaryId = $request->post('id');

        $dictionaryData = [];

        if (isset($request->name)) {
            $dictionaryData['name'] = $request->post('name');
        }

        if (isset($request->source_language)) {
            $dictionaryData['source_language'] = $request->post('source_language');
        }

        if (isset($request->target_language)) {
            $dictionaryData['target_language'] = $request->post('target_language');
        }

        if (isset($request->color)) {
            $dictionaryData['color'] = $request->post('color');
        }
        
        if (isset($request->enabled)) {
            $dictionaryData['enabled'] = $request->post('enabled');
        }

        try {
            $this->dictionaryService->updateDictionary($dictionaryId, $dictionaryData);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json('Dictionary has been updated successfully.', 200);
    }

    public function isDeeplEnabled() {
        $language = Auth::user()->selected_language;

        try {
            $response = $this->dictionaryService->isDeeplEnabled($language);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
        
        return response()->json($response, 200);
    }

    public function getDeeplCharacterLimit() {
        try {
            $deeplLimit = $this->dictionaryService->getDeeplCharacterLimit();   
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
        
        return response()->json($deeplLimit, 200);
    }

    public function searchDefinitions(SearchDefinitionsRequest $request) {
        $language = $request->post('language');
        $term = $request->post('term');

        try {
            $searchResult = $this->dictionaryService->searchDefinitions($language, $term);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($searchResult, 200);
    }

    /*
        This function returns a list of exact matches from dictionaries for the hover popup vocabulary.
    */
    public function searchDefinitionsForHoverVocabulary(SearchDefinitionsForHoverVocabularyRequest $request) {
        $language = $request->post('language');
        $term = $request->post('term');

        try {
            $searchResult = $this->dictionaryService->searchDefinitionsForHoverVocabulary($language, $term);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($searchResult, 200);
    }

    public function searchDeepl(SearchDeeplRequest $request) {
        $language = $request->post('language');
        $term = $request->post('term');

        try {
            $definitions = $this->dictionaryService->searchDeepl($language, $term);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        $result = new \stdClass();
        $result->term = $term;
        $result->definitions = $definitions;

        return response()->json($result, 200);
    }

    public function searchInflections(SearchInflectionsRequest $request) {
        $term = $request->term;

        try {
            $inflections = $this->dictionaryService->searchInflections($term);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($inflections, 200);
    }

    /*
        This function tests a .csv file, and returns a sample of the data.
        This makes it faster to test a file and notice any problems before
        the user actually imports a large file.
    */
    public function testDictionaryCsvFile(TestDictionaryCsvFileRequest $request) {
        $file = $request->file('dictionary');
        $delimiter = $request->post('delimiter') === null ? ' ' : $request->post('delimiter');
        $skipHeader = boolval($request->post('skipHeader') === 'true');

        try {
            $sample = $this->dictionaryService->testDictionaryCsvFile($file, $delimiter, $skipHeader);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($sample, 200);
    }

    public function createDeeplDictionary(CreateDeeplDictionaryRequest $request) {
        $sourceLanguage = $request->post('sourceLanguage');
        $targetLanguage = $request->post('targetLanguage');
        $color = $request->post('color');
        $name  = $request->post('name');

        try {
            $this->dictionaryImportService->createDeeplDictionary($sourceLanguage, $targetLanguage, $color, $name);
        } catch(\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json('DeepL dictionary has been created successfully.', 200);
    }

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

                if (mb_strlen($record[0]) > 255 || mb_strlen($record[1]) > 2047) {
                    continue;
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

    public function getDictionaryFileInformation(GetDictionaryFileInformationRequest $request) {
        $dictionaryFile = $request->file('dictionaryFile');
        $dictCcLanguageCodes = config('linguacafe.languages.dict_cc_language_codes');
        $databaseLanguageCodes = config('linguacafe.languages.database_name_language_codes');
        $supportedSourceLanguages = config('linguacafe.languages.supported_languages');
        
        try {
            $dictionariesFound = $this->dictionaryImportService->getDictionaryFileInformation($dictionaryFile, $supportedSourceLanguages, $dictCcLanguageCodes, $databaseLanguageCodes);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
        
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
                $this->dictionaryImportService->jmdictImport();
                $this->dictionaryImportService->kanjiImport();
                $this->dictionaryImportService->kanjiRadicalImport();
            } catch (\Throwable $t) {
                return $t->getMessage();
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            return 'success';
        }

        // import cc cedict or HanDeDict file
        if ($dictionaryName == 'cc-cedict' || $dictionaryName == 'HanDeDict') {
            try {
                $this->dictionaryImportService->importCeDictOrHanDeDict($dictionaryName, $dictionaryTargetLanguage, $dictionaryDatabaseName, $dictionaryFileName);
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
                $this->dictionaryImportService->importKengdic($dictionaryName, $dictionaryDatabaseName, $dictionaryFileName);
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
                $this->dictionaryImportService->importEurfa($dictionaryName, $dictionaryDatabaseName, $dictionaryFileName);
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
                $this->dictionaryImportService->importDictCc(
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
                $this->dictionaryImportService->importWiktionary(
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

    public function deleteDictionary($dictionaryId) {
        try {
            $dictionary = Dictionary
                ::where('id', $dictionaryId)
                ->first();
            
            if ($dictionary->database_table_name !== 'API') {
                Schema::drop($dictionary->database_table_name);
            }
            
            Dictionary::where('id', $dictionaryId)->delete();
        } catch (\Exception $exception) {
            return 'error';
        }

        return 'success';
    }
}
