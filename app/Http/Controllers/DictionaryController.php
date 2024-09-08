<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dictionaries\CreateCustomApiDictionaryRequest;
use App\Models\Dictionary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\DictionaryService;
use Illuminate\Support\Facades\Auth;

// services
use Illuminate\Support\Facades\Schema;
use App\Services\DictionaryImportService;

// request classes
use App\Http\Requests\Dictionaries\SearchApiRequest;
use App\Http\Requests\Dictionaries\GetDictionaryRequest;
use App\Http\Requests\Dictionaries\DeleteDictionaryRequest;
use App\Http\Requests\Dictionaries\UpdateDictionaryRequest;
use App\Http\Requests\Dictionaries\SearchDefinitionsRequest;
use App\Http\Requests\Dictionaries\SearchInflectionsRequest;
use App\Http\Requests\Dictionaries\CreateDeeplDictionaryRequest;
use App\Http\Requests\Dictionaries\TestDictionaryCsvFileRequest;
use App\Http\Requests\Dictionaries\ImportDictionaryCsvFileRequest;
use App\Http\Requests\Dictionaries\CreateMyMemoryDictionaryRequest;
use App\Http\Requests\Dictionaries\GetDictionaryRecordCountRequest;
use App\Http\Requests\Dictionaries\ImportSupportedDictionaryRequest;
use App\Http\Requests\Dictionaries\GetDictionaryFileInformationRequest;
use App\Http\Requests\Dictionaries\CreateLibreTranslateDictionaryRequest;
use App\Http\Requests\Dictionaries\SearchDefinitionsForHoverVocabularyRequest;

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

        if (isset($request->api_host)) {
            $dictionaryData['api_host'] = $request->post('api_host');
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

    public function isAnyApiDictionaryEnabled() {
        $language = Auth::user()->selected_language;

        try {
            $response = $this->dictionaryService->isAnyApiDictionaryEnabled($language);
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

    public function searchApiDictionaries(SearchApiRequest $request) {
        $language = $request->post('language');
        $term = $request->post('term');

        try {
            $definitions = $this->dictionaryService->searchApiDictionaries($language, $term);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($definitions, 200);
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

    public function createMyMemoryDictionary(CreateMyMemoryDictionaryRequest $request) {
        $sourceLanguage = $request->validated('sourceLanguage');
        $targetLanguage = $request->validated('targetLanguage');
        $color = $request->validated('color');
        $name  = $request->validated('name');

        try {
            $this->dictionaryImportService->createMyMemoryDictionary($sourceLanguage, $targetLanguage, $color, $name);
        } catch(\Exception $e) {
            abort(500, $e->getMessage());
        }
    }
    
    public function createLibreTranslateDictionary(CreateLibreTranslateDictionaryRequest $request) {
        $sourceLanguage = $request->validated('sourceLanguage');
        $targetLanguage = $request->validated('targetLanguage');
        $color = $request->validated('color');
        $name  = $request->validated('name');

        try {
            $this->dictionaryImportService->createLibreTranslateDictionary($sourceLanguage, $targetLanguage, $color, $name);
        } catch(\Exception $e) {
            abort(500, $e->getMessage());
        }
    }

    public function createCustomApiDictionary(CreateCustomApiDictionaryRequest $request) {
        $sourceLanguage = $request->validated('sourceLanguage');
        $targetLanguage = $request->validated('targetLanguage');
        $color = $request->validated('color');
        $name  = $request->validated('name');
        $host  = $request->validated('api_host');

        try {
            $this->dictionaryImportService->createCustomApiDictionary($sourceLanguage, $targetLanguage, $color, $name, $host);
        } catch(\Exception $e) {
            abort(500, $e->getMessage());
        }
    }

    /*
        This function tests a .csv file, and returns a sample of the data.
        This makes it faster to test a file and notice any problems before
        the user actually imports a large file.
    */
    public function testDictionaryCsvFile(TestDictionaryCsvFileRequest $request) {
        $file = $request->file('dictionary');
        $delimiter = $request->post('delimiter');
        $skipHeader = boolval($request->post('skipHeader') === 'true');

        try {
            $sample = $this->dictionaryImportService->testDictionaryCsvFile($file, $delimiter, $skipHeader);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($sample, 200);
    }

    public function importDictionaryCsvFile(ImportDictionaryCsvFileRequest $request) {
        set_time_limit(2400);
        $file = $request->file('dictionary');
        $skipHeader = boolval($request->post('skipHeader') === 'true');
        $delimiter = $request->post('delimiter');
        $dictionaryName = $request->post('dictionaryName');
        $databaseTableName = $request->post('databaseName');
        $sourceLanguage = $request->post('sourceLanguage');
        $targetLanguage = $request->post('targetLanguage');
        $color = $request->post('color');

        try {
            $this->dictionaryImportService->importDictionaryCsvFile(
                $file, 
                $skipHeader, 
                $delimiter, 
                $dictionaryName, 
                $databaseTableName, 
                $sourceLanguage, 
                $targetLanguage, 
                $color
            );

        } catch(\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json('Dictionary has been imported successfully.', 200);
    }

    public function getDictionaryFileInformation(GetDictionaryFileInformationRequest $request) {
        $dictionaryFile = $request->file('dictionaryFile');
        $dictCcLanguageCodes = config('linguacafe.languages.dict_cc_language_codes');
        $databaseLanguageCodes = config('linguacafe.languages.database_name_language_codes');
        $supportedSourceLanguages = config('linguacafe.languages.supported_languages');
        
        try {
            $dictionariesFound = $this->dictionaryImportService->getDictionaryFileInformation(
                $dictionaryFile, 
                $supportedSourceLanguages, 
                $dictCcLanguageCodes, 
                $databaseLanguageCodes
            );
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
        
        return json_encode($dictionariesFound);
    }

    public function importSupportedDictionary(ImportSupportedDictionaryRequest $request) {
        set_time_limit(2400);
        $userUuid = Auth::user()->uuid;
        $dictionaryName = $request->post('dictionaryName');
        $dictionaryFileName = $request->post('dictionaryFileName');
        $dictionarySourceLanguage = $request->post('dictionarySourceLanguage');
        $dictionaryTargetLanguage = $request->post('dictionaryTargetLanguage');
        $dictionaryDatabaseName = $request->post('dictionaryDatabaseName');
        
        try {
            $this->dictionaryImportService->importSupportedDictionary(
                $userUuid, 
                $dictionaryName, 
                $dictionaryFileName, 
                $dictionarySourceLanguage, 
                $dictionaryTargetLanguage, 
                $dictionaryDatabaseName
            );
        } catch (\Throwable $t) {
            if ($dictionaryName !== 'JMDict') {
                DB
                    ::table('dictionaries')
                    ->where('database_table_name', $dictionaryDatabaseName)
                    ->delete();

                Schema::dropIfExists($dictionaryDatabaseName);
            }
            
            abort(500, $t->getMessage());
        } catch (\Exception $e) {
            if ($dictionaryName !== 'JMDict') {
                DB
                    ::table('dictionaries')
                    ->where('database_table_name', $dictionaryDatabaseName)
                    ->delete();

                Schema::dropIfExists($dictionaryDatabaseName);
            }
            
            abort(500, $e->getMessage());
        }

        return response()->json('Dictionary has been imported successfully.', 200);
    }

    public function getDictionaryRecordCount($dictionaryTableName, GetDictionaryRecordCountRequest $request) {
        try {
            $recordCount = $this->dictionaryService->getDictionaryRecordCount($dictionaryTableName);
        } catch(\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($recordCount, 200);
    }

    public function deleteDictionary($dictionaryId, DeleteDictionaryRequest $request) {
        try {
            $this->dictionaryService->deleteDictionary($dictionaryId);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json('Dictionary has been deleted successfully.', 200);
    }
}
