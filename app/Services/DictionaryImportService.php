<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Kanji;
use League\Csv\Reader;
use App\Models\Radical;
use App\Models\Dictionary;
use App\Models\VocabularyJmdict;
use Illuminate\Support\Facades\DB;
use App\Models\VocabularyJmdictWord;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use App\Models\VocabularyJmdictReading;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Schema\Blueprint;

class DictionaryImportService {

    public function __construct() {
    }

    private function deleteTempDictionaryFiles() {
        $tempDictionaryFiles = Storage::allFiles('temp/dictionaries');
        Storage::delete($tempDictionaryFiles);
    }

    public function getDictionaryFileInformation($dictionaryFile, $supportedSourceLanguages, $dictCcLanguageCodes, $databaseLanguageCodes) {
        // delete old files from dictionaries temp folder
        $this->deleteTempDictionaryFiles();

        // move uploaded file to the dictionaries temp folder
        $fileName = $dictionaryFile->getClientOriginalName();
        $dictionaryFile->move(storage_path('app/temp/dictionaries'), $fileName);

        // scan the new file
        $dictionary = null;
        
        // jmdict dictionary
        if ($fileName === 'jmdict.zip') {
            $dictionary = new \stdClass();
            $dictionary->name = 'JMDict';
            $dictionary->databaseName = 'dict_jp_jmdict';
            $dictionary->source_language = 'japanese';
            $dictionary->target_language = 'english';
            $dictionary->color = '#74E39A'; 
            $dictionary->expectedRecordCount = 207690;
            $dictionary->fileName = 'jmdict.zip';

            // check if jmdict is imported
            $recordCount = DB::table('dict_jp_jmdict')->count();
        }

        // cc cedict dictionary
        if ($fileName === 'cedict_ts.u8') {
            $dictionary = new \stdClass();
            $dictionary->name = 'cc-cedict';
            $dictionary->databaseName = 'dict_zh_cedict';
            $dictionary->source_language = 'chinese';
            $dictionary->target_language = 'english';
            $dictionary->color = '#EF4556'; 
            $dictionary->expectedRecordCount = 0;
            $dictionary->fileName = 'cedict_ts.u8';

            // check record count
            $handle = fopen(Storage::path('temp/dictionaries/cedict_ts.u8'), "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (str_contains($line, '#! entries=')) {
                        $dictionary->expectedRecordCount = intval(explode('#! entries=', $line)[1]);
                        break;
                    }
                }
            }
            
            // close file
            fclose($handle);
        }

        // HanDeDict dictionary
        if ($fileName === 'handedict.u8') {
            $dictionary = new \stdClass();
            $dictionary->name = 'HanDeDict';
            $dictionary->databaseName = 'dict_zh_handedict';
            $dictionary->source_language = 'chinese';
            $dictionary->target_language = 'german';
            $dictionary->color = '#EF4556'; 
            $dictionary->expectedRecordCount = 0;
            $dictionary->fileName = 'handedict.u8';

            // check record count
            $dictionary->expectedRecordCount = $this->getFileLineCount(Storage::path('temp/dictionaries/handedict.u8'));
        }

        // kengdic dictionary
        if ($fileName === 'kengdic.tsv') {
            $dictionary = new \stdClass();
            $dictionary->name = 'kengdic';
            $dictionary->databaseName = 'dict_ko_kengdic';
            $dictionary->source_language = 'korean';
            $dictionary->target_language = 'english';
            $dictionary->color = '#DDBFE4'; 
            $dictionary->expectedRecordCount =  117509;
            $dictionary->fileName = 'kengdic.tsv';

            return $dictionary;
        }

        // eurfa welsh dictionary
        if ($fileName === 'Eurfa_Welsh_Dictionary.csv') {
            $dictionary = new \stdClass();
            $dictionary->name = 'eurfa';
            $dictionary->databaseName = 'dict_cy_eurfa';
            $dictionary->source_language = 'welsh';
            $dictionary->target_language = 'english';
            $dictionary->color = '#32DB4D'; 
            $dictionary->expectedRecordCount =  210579;
            $dictionary->fileName = 'Eurfa_Welsh_Dictionary.csv';

            return $dictionary;
        }
        
        
        // dict cc dictionaries
        if (pathinfo($fileName, PATHINFO_EXTENSION) === 'txt') {
            $supported = true;

            // get language
            $handle = fopen(Storage::path('temp/dictionaries/' . $fileName), "r");
            if ($handle) {
                if (($line = fgets($handle)) !== false) {
                    # example line:
                    # FI-EN vocabulary database	compiled by dict.cc

                    // skip file if it's not a dict cc dictionary
                    if (!str_contains($line, ' vocabulary database	compiled by dict.cc')) {
                        $supported = false;
                    }

                    // split first line by spaces
                    $words = explode(' ', $line);

                    // split second word by '-' character.
                    if (count($words) > 1) {
                        $fileLanguage = explode('-', $words[1]);

                        // skip not supported languages
                        if (
                            !isset($dictCcLanguageCodes[$fileLanguage[0]]) || 
                            !isset($dictCcLanguageCodes[$fileLanguage[1]]) || 
                            !in_array(ucfirst($dictCcLanguageCodes[$fileLanguage[0]]), $supportedSourceLanguages, true)
                            
                        ) {
                            $supported = false;
                        }
                    }
                }
            }

            // close file
            fclose($handle);

            // add the found dictionary to the list
            if ($supported) {
                $dictionary = new \stdClass();
                $dictionary->name = 'dictcc ' . $databaseLanguageCodes[$dictCcLanguageCodes[$fileLanguage[0]]] . '-'. $databaseLanguageCodes[$dictCcLanguageCodes[$fileLanguage[1]]];
                $dictionary->databaseName = 'dict_' . $databaseLanguageCodes[$dictCcLanguageCodes[$fileLanguage[0]]] . '_' . $databaseLanguageCodes[$dictCcLanguageCodes[$fileLanguage[1]]] . '_dict_cc';
                $dictionary->source_language = $dictCcLanguageCodes[$fileLanguage[0]];
                $dictionary->target_language = $dictCcLanguageCodes[$fileLanguage[1]];
                $dictionary->color = '#FF981B'; 
                $dictionary->expectedRecordCount = $this->getFileLineCount(Storage::path('temp/dictionaries/' . $fileName));
                $dictionary->fileName = $fileName;

                return $dictionary;
            }
        }

        // wiktionary dictionaries
        if (pathinfo($fileName, PATHINFO_EXTENSION) === 'tsv') {
            $supported = true;

            // get filename and split into words
            $words = explode('.', pathinfo($fileName, PATHINFO_FILENAME));

            // make sure the file is in a format that's expected
            if (count($words) < 2) {
                $supported = false;
            }

            // skip file if it's not a wiktionary
            if ($words[1] !== 'wiktionary') {
                $supported = false;
            }

            // get language
            $language = strtolower($words[0]);

            if ($supported) {
                $dictionary = new \stdClass();
                $dictionary->name = 'wiktionary ' . $databaseLanguageCodes[$language];
                $dictionary->databaseName = 'dict_' . $databaseLanguageCodes[$language] . '_wiktionary';
                $dictionary->source_language = $language;
                $dictionary->target_language = 'english';
                $dictionary->color = '#E9CDA0'; 
                $dictionary->expectedRecordCount = $this->getFileLineCount(Storage::path('temp/dictionaries/' . $fileName));
                $dictionary->fileName =  $fileName;

                return $dictionary;
            }
        }

        return $dictionary;
    }

    private function getFileLineCount($fileName) {
        $lineCount = 0;
        $file = fopen($fileName, 'r');
        
        if ($file) {
            while(!feof($file)){
                $content = fgets($file);
                if($content) {
                    $lineCount ++;
                }
            }
        } else {
            return -1;
        }

        fclose($file);

        return $lineCount;
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
    
    public function importSupportedDictionary($userUuid, $dictionaryName, $dictionaryFileName, $dictionarySourceLanguage, $dictionaryTargetLanguage, $dictionaryDatabaseName) {
        set_time_limit(2400);
        
        // import jmdict files
        if ($dictionaryName == 'JMDict') {
            $this->jmdictImport($userUuid);
            $this->kanjiImport();
            $this->kanjiRadicalImport();

            return true;
        }

        // import cc cedict or HanDeDict file
        if ($dictionaryName == 'cc-cedict' || $dictionaryName == 'HanDeDict') {
            $this->importCeDictOrHanDeDict($userUuid, $dictionaryName, $dictionaryTargetLanguage, $dictionaryDatabaseName, $dictionaryFileName);

            return true;
        }

        // import kengdic file
        if ($dictionaryName == 'kengdic') {
            $this->importKengdic($userUuid, $dictionaryName, $dictionaryDatabaseName, $dictionaryFileName);

            return true;
        }

        // import eurfa files
        if ($dictionaryName == 'eurfa') {
            $this->importEurfa($userUuid, $dictionaryName, $dictionaryDatabaseName, $dictionaryFileName);

            return true;
        }
        

        // import dict cc files
        if (str_contains($dictionaryName, 'dictcc')) {
            $this->importDictCc(
                $userUuid, 
                $dictionaryName, 
                $dictionarySourceLanguage, 
                $dictionaryTargetLanguage,
                $dictionaryFileName, 
                $dictionaryDatabaseName
            );

            return true;
        }

        // import wiktionary files
        if (str_contains($dictionaryName, 'wiktionary')) {
            $this->importWiktionary(
                $userUuid, 
                $dictionaryName, 
                $dictionarySourceLanguage, 
                $dictionaryFileName, 
                $dictionaryDatabaseName
            );
            
            return true;
        }
    }
    
    /*
        Imports a cc-cedict or HanDeDict dictionary file into the database.
        They are in the same format, HanDeDict is just translated to German.
    */
    public function importCeDictOrHanDeDict($userUuid, $dictionaryName, $targetLanguage, $databaseTableName, $fileName) {
        $this->createDatabase($dictionaryName, $databaseTableName, 'chinese', $targetLanguage, '#EF4556');

        $index = 0;
        DB::beginTransaction();

        $handle = fopen(Storage::path('temp/dictionaries/' . $fileName), "r");
        
        if (!$handle) {
            return 'error';
        }

        while (($line = fgets($handle)) !== false) {
            // skip comments
            if ($line[0] == '#') {  
                continue;
            }

            $data = explode(' ', $line);

            // skip possible empty rows
            if (count($data) < 2) {
                continue;
            }

            // collect definitions
            $definitions = explode('/', $line);
            array_shift($definitions);
            array_pop($definitions);
            $definitions = implode(';', $definitions);


            DB::table($databaseTableName)->insert([
                'word' => mb_strtolower($data[1], 'UTF-8'),
                'definitions' => mb_strtolower($definitions, 'UTF-8'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            if ($index % 1000 == 0) {
                DB::commit();
                DB::beginTransaction();

                // send progress through websockets
                event(new \App\Events\DictionaryImportProgressedEvent($userUuid, $index));
            }
            
            $index ++;
        }

        DB::commit();
        fclose($handle);
        
        return 'success';
    }

    /*
        Imports a kengdic dictionary file into the database.
    */
    public function importKengdic($userUuid, $dictionaryName, $databaseTableName, $fileName) {
        $this->createDatabase($dictionaryName, $databaseTableName, 'korean', 'english', '#DDBFE4');

        $index = 0;
        DB::beginTransaction();
        $handle = fopen(Storage::path('temp/dictionaries/' . $fileName), "r");
        
        if (!$handle) {
            return 'error';
        }

        while (($line = fgets($handle)) !== false) {
            // skip first line
            if (str_contains($line, 'id	surface')) {
                continue;
            }

            $data = explode('	', $line);

            // skip possible empty rows
            if (count($data) < 4) {
                continue;
            }

            // skip empty definitions
            if (strlen(trim($data[3])) == 0) {
                continue;
            }


            DB::table($databaseTableName)->insert([
                'word' => mb_strtolower($data[1], 'UTF-8'),
                'definitions' => mb_strtolower($data[3], 'UTF-8'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            if ($index % 1000 == 0) {
                DB::commit();
                DB::beginTransaction();

                // send progress through websockets
                event(new \App\Events\DictionaryImportProgressedEvent($userUuid, $index));
            }
            
            $index ++;
        }

        DB::commit();
        fclose($handle);
        
        return 'success';
    }

    /*
        Imports a  dictionary file into the database.
    */
    public function importEurfa($userUuid, $dictionaryName, $databaseTableName, $fileName) {
        $this->createDatabase($dictionaryName, $databaseTableName, 'welsh', 'english', '#32DB4D');

        DB::beginTransaction();
        $index = 0;
        $csv = Reader::createFromPath(storage_path('app/temp/dictionaries') . '/' . $fileName, 'r');
        $records = $csv->getRecords();
        foreach ($records as$record) {

            // check if both columns exist
            if (!isset($record[1]) || !isset($record[2]) || !isset($record[3])) {
                throw new \Exception('Missing data.');
            }

            // add word 
            DB::table($databaseTableName)->insert([
                'word' => mb_strtolower($record[1], 'UTF-8'),
                'definitions' => $record[3]
            ]);

            // add lemma too, because there is no lemmatisation for welsh
            DB::table($databaseTableName)->insert([
                'word' => mb_strtolower($record[2], 'UTF-8'),
                'definitions' => $record[3],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            if ($index % 1000 == 0) {
                DB::commit();
                DB::beginTransaction();

                // send progress through websockets
                event(new \App\Events\DictionaryImportProgressedEvent($userUuid, $index));
            }
            
            $index ++;
        }

        DB::commit();
        
        return 'success';
    }

    /*
        Imports a dict cc dictionary file into the database.
    */
    public function importDictCc($userUuid, $dictionaryName, $sourceLanguage, $targetLanguage, $fileName, $databaseTableName) {
        $this->createDatabase($dictionaryName, $databaseTableName, $sourceLanguage, $targetLanguage, '#FF981B');

        $index = 0;
        DB::beginTransaction();

        $handle = fopen(Storage::path('temp/dictionaries/' . $fileName), "r");
        if (!$handle) {
            return 'error';
        }

        while (($line = fgets($handle)) !== false) {
            // skip comments
            if ($line[0] == '#') {  
                continue;
            }

            $data = explode('	', $line);

            // skip empty rows
            if (count($data) < 2) {
                continue;
            }

            DB::table($databaseTableName)->insert([
                'word' => mb_strtolower($data[0], 'UTF-8'),
                'definitions' => mb_strtolower($data[1], 'UTF-8'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            if ($index % 1000 == 0) {
                DB::commit();
                DB::beginTransaction();

                // send progress through websockets
                event(new \App\Events\DictionaryImportProgressedEvent($userUuid, $index));
            }
            
            $index ++;
        }

        DB::commit();
        fclose($handle);
        
        return 'success';
    }

    /*
        Imports a wiktionary dictionary file into the database.
    */
    public function importWiktionary($userUuid, $dictionaryName, $sourceLanguage, $fileName, $databaseTableName) {
        $this->createDatabase($dictionaryName, $databaseTableName, $sourceLanguage, 'english', '#E9CDA0');

        $index = 0;
        DB::beginTransaction();
        $handle = fopen(Storage::path('temp/dictionaries/' . $fileName), "r");
        if (!$handle) {
            return 'error';
        }

        while (($line = fgets($handle)) !== false) {
            
            $data = explode('	', $line);

            // skip empty rows
            if (count($data) < 2) {
                continue;
            }

            // extract word
            $word = explode('|', $data[0])[0];
            $word = mb_strtolower($word, 'UTF-8');

            // extract definitions from <li> tags
            $filteredDefinitions = [];
            $definitions = mb_strtolower($data[1], 'UTF-8');
            $definitions = explode('<li>', $definitions);
            
            foreach ($definitions as $definitionCounter => $definition) {
                if (!$definitionCounter) {
                    continue;
                }

                $filteredDefinitions[] = explode('</li>', $definition)[0];
            }

            // join filtered definitions
            $filteredDefinitions = implode(';', $filteredDefinitions);

            // skip too long definitions
            if (strlen($filteredDefinitions) > 254) {
                continue;
            }

            DB::table($databaseTableName)->insert([
                'word' => $word,
                'definitions' => $filteredDefinitions,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            if ($index % 1000 == 0) {
                DB::commit();
                DB::beginTransaction();

                // send progress through websockets
                event(new \App\Events\DictionaryImportProgressedEvent($userUuid, $index));
            }
            
            $index ++;
        }

        DB::commit();
        fclose($handle);
        
        return 'success';
    }

    /*
        Imports kanji radicals.
    */
    public function kanjiRadicalImport() {
        // init
        DB::beginTransaction();
        $file = fopen(base_path() . '/storage/app/temp/dictionaries/radicals.txt', 'r');
        $index = 0;

        // these kanjis has to be replaced with radicals
        // based on the description of the input files
        $replacements = [
            '化' => '⺅',
            '个' => '𠆢',
            '并' => '丷',
            '刈' => '⺉',
            '込' => '⻌',
            '尚' => '⺌',
            '忙' => '⺖',
            '扎' => '扌',
            '汁' => '⺡',
            '犯' => '⺨',
            '艾' => '⺾',
            '邦' => '⻏',
            '阡' => '⻖',
            '老' => '⺹',
            '杰' => '⺣',
            '礼' => '⺭',
            '疔' => '⽧',
            '禹' => '⽱',
            '初' => '⻂',
            '買' => '⺲',
            '滴' => '啇',
            //乞 has no character, an image must be displayed
        ];


        // delete old database entries
        DB::statement('DELETE FROM dict_jp_kanji_radicals');
        
        // load radical stroke counts into an array
        $radicalStrokesFiles = fopen(base_path() . '/storage/app/temp/dictionaries/radical-strokes.txt', 'r');
        $radicalStrokeCountsData = [];

        while (($line = fgets($radicalStrokesFiles)) !== false) {
            $data = explode(' ', $line);
            $radicalStrokeCountsData[$data[0]] = $data[1];
        }

        // loop through the radicals files
        while (($line = fgets($file)) !== false) {
            // skip commented lines
            if ($line[0] == '#') {
                continue;
            }

            $data = explode(' : ', $line);
            $radicals = explode(' ', trim($data[1]));
            $processedRadicals = [];
            
            // collects the radicals into an array of objects
            // that contains both the radical and the stroke counts
            foreach($radicals as $radical) {
                $processedRadical = $radical;

                // replacing kanjis with radicals
                foreach($replacements as $original => $replacement) {
                    if ($processedRadical == $original) {
                        $processedRadical = $replacement;
                    }
                }

                $radicalObject = new \stdClass();
                $radicalObject->radical = $processedRadical;
                $radicalObject->strokes = $radicalStrokeCountsData[$radical];

                array_push($processedRadicals, $radicalObject);
            }

            // save radical
            $radical = new Radical();
            $radical->kanji = trim($data[0]);
            $radical->radicals = json_encode($processedRadicals);
            $radical->save();

            $index ++;
        }

        // finish
        DB::commit();
    }

    /*
        Imports kanji.
    */
    public function kanjiImport() {
        $jlpt = [
            '1' => 1,
            '2' => 2,
            '3' => 4,
            '4' => 5,
        ];

        DB::statement('DELETE FROM dict_jp_kanji');

        $doc = new \DOMDocument();
        $reader = new \XMLReader();
        $reader->open(base_path() . '/storage/app/temp/dictionaries/kanjidic2.xml');
        $index = 0;        

        DB::beginTransaction();
        while ($reader->read() && $reader->name !== 'character');
        while ($reader->name === 'character') {
            $node = simplexml_import_dom($doc->importNode($reader->expand(), true));
            
            $kanji = new Kanji();
            $kanji->kanji = $node->literal->__toString();
            $meanings = [];
            $readings_on = [];
            $readings_kun = [];
            $kanji->grade = 0;
            $kanji->strokes = 0;
            $kanji->frequency = 0;
            $kanji->jlpt = 0;

            // grade
            // 1-6 school
            // 8 Jouyou kanji
            // 9-10 Jinmeiyou kanji, used for names
            if (isset($node->misc->grade)) {
                $kanji->grade = intval($node->misc->grade->__toString());
                if ($kanji->grade == 9) {
                    $kanji->grade = 10;
                }
            }
            
            // stoke count
            if (isset($node->misc->stroke_count)) {
                $kanji->strokes = intval($node->misc->stroke_count->__toString());
            }

            // frequency (based on modern newspapers 1-2501)
            if (isset($node->misc->freq)) {
                $kanji->frequency = intval($node->misc->freq->__toString());
            }
            
            // jlpt level (2 is 2/3 in the new system)
            if (isset($node->misc->jlpt)) {
                $kanji->jlpt = $jlpt[$node->misc->jlpt->__toString()];
            }
            
            // readings
            if (isset($node->reading_meaning) && isset($node->reading_meaning->rmgroup) && count($node->reading_meaning->rmgroup->reading)) {
                for ($i = 0; $i < count($node->reading_meaning->rmgroup->reading); $i++) {
                    $element = $node->reading_meaning->rmgroup->reading[$i];
                    if (isset($element->attributes()->r_type)) {
                        
                        // on reading
                        if ($element->attributes()->r_type == 'ja_on') {
                            array_push($readings_on, $element->__toString());
                        }

                        // kun reading
                        if ($element->attributes()->r_type == 'ja_kun') {
                            array_push($readings_kun, $element->__toString());
                        }
                    }
                }
            }

            // meanings
            if (isset($node->reading_meaning) && isset($node->reading_meaning->rmgroup) && count($node->reading_meaning->rmgroup->meaning)) {
                for ($i = 0; $i < count($node->reading_meaning->rmgroup->meaning); $i++) {
                    $element = $node->reading_meaning->rmgroup->meaning[$i];
                    
                    // english meanings
                    if (!isset($element->attributes()->m_lang)) {
                        array_push($meanings, $element->__toString());
                    }   
                }
            }

            // save kanji in database
            $kanji->meanings = json_encode($meanings);
            $kanji->readings_on = json_encode($readings_on);
            $kanji->readings_kun = json_encode($readings_kun);
            $kanji->save();
            $index ++;
            $reader->next('character');
        }

        DB::commit();
    }

    /*
        Imports jmdict dictionary file.
    */
    public function jmdictImport($userUuid) {
        DB::statement('DELETE FROM dict_jp_jmdict');
        DB::statement('DELETE FROM dict_jp_jmdict_words');
        DB::statement('DELETE FROM dict_jp_jmdict_readings');

        // extract zip file
        $filePath = Storage::path('temp/dictionaries/jmdict.zip');
        $extractPath = Storage::path('temp/dictionaries');

        // extract jmdict.zip file
        $zip = new \ZipArchive();
        $zipFile = $zip->open($filePath);
        if ($zipFile === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();
        } else {
            throw new \Exception('JMDict zip file could not be extracted.');
        }

        // import jmdict file
        $file = fopen(base_path() . '/storage/app/temp/dictionaries/jmdict_processed.txt', 'r');
        $index = 0;
        DB::beginTransaction();
        while (($line = fgets($file)) !== false) {
            $data = explode('|', str_replace(["\r\n", "\r", "\n"], '', $line));
            
            // save main vocab model
            $vocabulary = new VocabularyJmdict();
            $vocabulary->translations = $data[2];

            if (mb_strlen($data[3]) > 2) {
                $vocabulary->conjugations = $data[3];
            } else {
                $vocabulary->conjugations = '';
            }

            $vocabulary->save();
            
            // save vocab words
            $words = explode(';', $data[0]);
            foreach ($words as $word) {
                $jmdictWord = new VocabularyJmdictWord();
                $jmdictWord->word = $word;
                $jmdictWord->dict_jp_jmdict_id = $vocabulary->id;
                $jmdictWord->save();
            }

            // save vocab readings
            $readings = explode(';', $data[1]);
            foreach ($readings as $reading) {
                $restrictions = explode('RE_RESTR', $reading);
                if (count($restrictions) > 1) {
                    $reading = array_shift($restrictions);
                    $restrictions = json_encode($restrictions);
                } else {
                    $reading = $restrictions[0];
                    $restrictions = '';
                }

                $jmdictReading = new VocabularyJmdictReading();
                $jmdictReading->reading = $reading;
                $jmdictReading->word_restrictions = $restrictions;
                $jmdictReading->dict_jp_jmdict_id = $vocabulary->id;
                $jmdictReading->save();
            }
            
            if ($index % 1000 == 0) {
                DB::commit();
                DB::beginTransaction();

                // send progress through websockets
                event(new \App\Events\DictionaryImportProgressedEvent($userUuid, $index));
            }
            
            $index ++;
        }   
        
        DB::commit();
        fclose($file);
        DB::table('dictionaries')->where('database_table_name', 'dict_jp_jmdict')->update(['enabled' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
    }

    /* 
        Converts jmdict to text. It is used to create the file that can be imported into linguacafe, it should be moved to python.
    */
    public function jmdictXmlToText() {
        $file = fopen(base_path() . '/storage/app/temp/dictionaries/jmdict.txt', 'w');
        $doc = new \DOMDocument();
        $reader = new \XMLReader();
        $reader->open(base_path() . '/storage/app/temp/dictionaries/JMdict_e.xml');
        $index = 0;
        

        while ($reader->read() && $reader->name !== 'entry');
        while ($reader->name === 'entry') {
            $entry = new \stdClass();
            $entry->all_words = '';
            $entry->all_readings = '';
            $node = simplexml_import_dom($doc->importNode($reader->expand(), true));
            
            // get all words
            if (isset($node->k_ele)) {
                // first word
                $entry->word = $node->k_ele[0]->keb->__toString();

                // all words
                for ($i = 0; $i < count($node->k_ele); $i++) {
                    if ($i) {
                        $entry->all_words .= ';';
                    }

                    $entry->all_words .= $node->k_ele[$i]->keb->__toString();
                }
            } else if (isset($node->r_ele)) {
                // use reading if there's no kanji word
                $entry->word = $node->r_ele[0]->reb->__toString();
            }

            // get all readings
            if (isset($node->r_ele)) {
                // all readings
                for ($i = 0; $i < count($node->r_ele); $i++) {
                    if ($i) {
                        $entry->all_readings .= ';';
                    }

                    $entry->all_readings .= $node->r_ele[$i]->reb->__toString();
                    if (isset($node->r_ele[$i]->re_restr)) {
                        for ($j = 0; $j < count($node->r_ele[$i]->re_restr); $j++) {
                            $entry->all_readings .= 'RE_RESTR' . $node->r_ele[$i]->re_restr[$j]->__toString();
                        }
                    }
                }
            }

            // get word translation and pos
            $entry->translations = [];
            $entry->pos = '';
            for ($i = 0; $i < count($node->sense); $i++) {
                // get restrictions for translation
                $restrictions = [];
                for ($j = 0; $j < count($node->sense[$i]->stagr); $j++) {
                    array_push($restrictions, $node->sense[$i]->stagr[$j]->__toString());
                }

                // definitions
                for ($j = 0; $j < count($node->sense[$i]->gloss); $j++) {
                    $translation = new \stdClass();
                    $translation->restrictions = $restrictions;
                    $translation->definition = $node->sense[$i]->gloss[$j]->__toString();
                    array_push($entry->translations, $translation);
                }

                // part of speech
                for ($j = 0; $j < count($node->sense[$i]->pos); $j++) {
                    // only need these conjugations in the output file
                    $conjugations = ["adj-i", "adj-ix", "adj-na", "v1", "v1-s", "v5aru", "v5b", "v5g", "v5k", "v5k-s", "v5m", "v5n", "v5r", "v5r-i", "v5s", "v5t", "v5u", "v5u-s", "vk", "vs", "vs-i", "vs-s"];
                    
                    if (mb_strlen($entry->word) > 1 && in_array(array_keys(get_object_vars($node->sense[$i]->pos[$j]))[0], $conjugations)) {
                        $entry->pos = array_keys(get_object_vars($node->sense[$i]->pos[$j]))[0];
                    }
                }
            }

            fwrite($file, $entry->word . '|' . $entry->all_words . '|' . $entry->all_readings . '|' . $entry->pos . '|' . json_encode($entry->translations) . "\r\n");
            $index ++;
            $reader->next('entry');
        }

        fclose($file);
        echo('finished');
    }

    public function createDeeplDictionary($sourceLanguage, $targetLanguage, $color, $name) {
        $dictionary = new Dictionary();
        $dictionary->name = $name;
        $dictionary->type = 'deepl';
        $dictionary->database_table_name = 'API';
        $dictionary->source_language = $sourceLanguage;
        $dictionary->target_language = $targetLanguage;
        $dictionary->color = $color;
        $dictionary->enabled = true;
        $dictionary->save();

        return true;
    }

    public function createMyMemoryDictionary($sourceLanguage, $targetLanguage, $color, $name) {
        $dictionary = new Dictionary();
        $dictionary->name = $name;
        $dictionary->type = 'my_memory';
        $dictionary->database_table_name = 'API';
        $dictionary->source_language = $sourceLanguage;
        $dictionary->target_language = $targetLanguage;
        $dictionary->color = $color;
        $dictionary->enabled = true;
        $dictionary->save();

        return true;
    }

    public function createLibreTranslateDictionary($sourceLanguage, $targetLanguage, $color, $name) {
        $dictionary = new Dictionary();
        $dictionary->name = $name;
        $dictionary->type = 'libre_translate';
        $dictionary->database_table_name = 'API';
        $dictionary->source_language = $sourceLanguage;
        $dictionary->target_language = $targetLanguage;
        $dictionary->color = $color;
        $dictionary->enabled = true;
        $dictionary->save();

        return true;
    }

    public function createCustomApiDictionary($sourceLanguage, $targetLanguage, $color, $name, $host) {
        $dictionary = new Dictionary();
        $dictionary->name = $name;
        $dictionary->type = 'custom_api';
        $dictionary->api_host = $host;
        $dictionary->database_table_name = 'API';
        $dictionary->source_language = $sourceLanguage;
        $dictionary->target_language = $targetLanguage;
        $dictionary->color = $color;
        $dictionary->enabled = true;
        $dictionary->save();

        return true;
    }

    private function createDatabase(string $dictionaryName, string $databaseTableName, string $sourceLanguage, string $targetLanguage, string $color): void
    {
        // create database table
        Schema::dropIfExists($databaseTableName);
        Schema::create($databaseTableName, function (Blueprint $table) {
            $table->id();
            $table->string('word', 256)->collation('utf8mb4_bin')->index();
            $table->string('definitions', 2048)->collation('utf8mb4_bin');
            $table->timestamps();
        });

        // insert dictionary to the dictionaries table
        $dictionary = DB::table('dictionaries')->where('name', $databaseTableName)->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => $dictionaryName,
                'database_table_name' => $databaseTableName,
                'source_language' => $sourceLanguage,
                'target_language' => $targetLanguage,
                'color' => $color,
                'enabled' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}