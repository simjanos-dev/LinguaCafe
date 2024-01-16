<?php

namespace App\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;
use App\Models\Kanji;
use App\Models\Radical;
use App\Models\Lesson;
use App\Models\VocabularyJmdict;
use App\Models\VocabularyJmdictWord;
use App\Models\VocabularyJmdictReading;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DictionaryImportService
{
    /*
        Scans the /storage/app/dictionaries folder, 
        and returns a list of importable dictionaries.
    */
    public function getImportableDictionaryList($dictCcLanguageCodes, $databaseLanguageCodes) {
        $dictionariesFound = [];
        $files = Storage::files('dictionaries');

        // jmdict dictionary
        if (Storage::exists('dictionaries/jmdict_processed.txt') &&
            Storage::exists('dictionaries/kanjidic2.xml') &&
            Storage::exists('dictionaries/radical-strokes.txt') &&
            Storage::exists('dictionaries/radicals.txt')) {
            
            $dictionary = new \stdClass();
            $dictionary->name = 'JMDict';
            $dictionary->databaseName = 'dict_jp_jmdict';
            $dictionary->language = 'japanese';
            $dictionary->color = '#74E39A'; 
            $dictionary->expectedRecordCount = 207690;
            $dictionary->firstUpdateInterval = 25000;
            $dictionary->updateInterval = 10000;
            $dictionary->fileName = 'multiple files';
            $dictionary->imported = false;

            // check if jmdict is imported
            $recordCount = DB::table('dict_jp_jmdict')->count();
            /*
                jmdict table always exist, so I check if it has been
                imported with this arbitrary number.
            */
            if ($recordCount > 180000) {
                $dictionary->imported = true;
            }


            // add jmdict to the list
            $dictionariesFound[] = $dictionary;
        }

        // cc cedict dictionary
        if (Storage::exists('dictionaries/cedict_ts.u8')) {
            $dictionary = new \stdClass();
            $dictionary->name = 'cc-cedict';
            $dictionary->databaseName = 'dict_zh_cedict';
            $dictionary->language = 'chinese';
            $dictionary->color = '#EF4556'; 
            $dictionary->expectedRecordCount = 0;
            $dictionary->firstUpdateInterval = 25000;
            $dictionary->updateInterval = 10000;
            $dictionary->fileName = 'cedict_ts.u8';
            $dictionary->imported = false;

            // check if cc cedict is imported
            if (Schema::hasTable($dictionary->databaseName)) {
                $dictionary->imported = true;
            }

            // check record count
            $handle = fopen(Storage::path('dictionaries/cedict_ts.u8'), "r");
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

            // add cc cedict to the list
            $dictionariesFound[] = $dictionary;
        }

        // dict cc dictionaries
        foreach ($files as $file) {
            // skip non txt files
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'txt') {
                continue;
            }
            
            // get language
            $handle = fopen(Storage::path($file), "r");
            if ($handle) {
                if (($line = fgets($handle)) !== false) {
                    # example line:
                    # FI-EN vocabulary database	compiled by dict.cc

                    // skip file if it's not a dict cc dictionary
                    if (!str_contains($line, ' vocabulary database	compiled by dict.cc')) {
                        continue;
                    }

                    // split first line by spaces
                    $words = explode(' ', $line);

                    // split second word by '-' character.
                    if (count($words) > 1) {
                        $fileLanguage = explode('-', $words[1]);

                        // if language code does not exist in config file, 
                        // skip this file
                        if (!isset($dictCcLanguageCodes[$fileLanguage[0]])) {
                            continue;
                        }
                    }
                }
            }

            // close file
            fclose($handle);

            // add the found dictionary to the list
            $dictionary = new \stdClass();
            $dictionary->name = 'dict cc ' . $databaseLanguageCodes[$dictCcLanguageCodes[$fileLanguage[0]]];
            $dictionary->databaseName = 'dict_' . $databaseLanguageCodes[$dictCcLanguageCodes[$fileLanguage[0]]] . '_dict_cc';
            $dictionary->language = $dictCcLanguageCodes[$fileLanguage[0]];
            $dictionary->color = '#FF981B'; 
            $dictionary->expectedRecordCount = count(file(Storage::path($file)));
            $dictionary->firstUpdateInterval = 3000;
            $dictionary->updateInterval = 10000;
            $dictionary->fileName = pathinfo($file, PATHINFO_BASENAME);
            $dictionary->imported = false;


            // check if the dictionary has been imported
            if (Schema::hasTable($dictionary->databaseName)) {
                $dictionary->imported = true;
            }
            
            // add dictionary to the list
            $dictionariesFound[] = $dictionary;
        }

        // wiktionary dictionaries
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'tsv') {
                continue;
            }

            // get filename and split into words
            $fileName = pathinfo($file, PATHINFO_FILENAME);
            $words = explode('.', $fileName);

            // make sure the file is in a format that's expected
            if (count($words) < 2) {
                continue;
            }

            // skip file if it's not a wiktionary
            if ($words[1] !== 'wiktionary') {
                continue;
            }

            // get language
            $language = strtolower($words[0]);

            $dictionary = new \stdClass();
            $dictionary->name = 'wiktionary ' . $databaseLanguageCodes[$language];
            $dictionary->databaseName = 'dict_' . $databaseLanguageCodes[$language] . '_wiktionary';
            $dictionary->language = $language;
            $dictionary->color = '#E9CDA0'; 
            $dictionary->expectedRecordCount = count(file(Storage::path($file)));
            $dictionary->firstUpdateInterval = 5000;
            $dictionary->updateInterval = 10000;
            $dictionary->fileName =  pathinfo($file, PATHINFO_BASENAME);

            // check if the wiktionary has been imported
            if (Schema::hasTable($dictionary->databaseName)) {
                $dictionary->imported = true;
            }
            
            // add wiktionary to the list
            $dictionariesFound[] = $dictionary;
        }

        return $dictionariesFound;
    }

    /*
    */
    public function importCedict($name, $databaseName, $fileName) {
        // create dictionary table 
        Schema::dropIfExists($databaseName);
        Schema::create($databaseName, function (Blueprint $table) {
            $table->id();
            $table->string('word', 256)->collation('utf8mb4_bin')->index();
            $table->string('definitions', 2048)->collation('utf8mb4_bin');
            $table->timestamps();
        });

        // add dictionary to the dictionaries table
        $dictionary = DB::table('dictionaries')->where('name', $name)->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => $name,
                'database_table_name' => $databaseName,
                'language' => 'chinese',
                'color' => '#EF4556',
                'imported' => true,
                'enabled' => true
            ]);
        }

        $index = 0;
        DB::beginTransaction();

        $handle = fopen(Storage::path('dictionaries/' . $fileName), "r");
        
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
            

            DB::table($databaseName)->insert([
                'word' => mb_strtolower($data[1], 'UTF-8'),
                'definitions' => mb_strtolower($definitions, 'UTF-8')
            ]);

            if ($index % 1000 == 0) {
                DB::commit();
                DB::beginTransaction();
            }
            
            $index ++;
        }

        DB::commit();
        fclose($handle);
        
        return 'success';
    }

    /*
        Imports a dict cc dictionary file into the database.
    */
    public function importDictCc($name, $language, $fileName, $databaseName) {
        // create dictionary table 
        Schema::dropIfExists($databaseName);
        Schema::create($databaseName, function (Blueprint $table) {
            $table->id();
            $table->string('word', 256)->collation('utf8mb4_bin')->index();
            $table->string('definitions', 2048)->collation('utf8mb4_bin');
            $table->timestamps();
        });

        // add dictionary to the dictionaries table
        $dictionary = DB::table('dictionaries')->where('name', $name)->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => $name,
                'database_table_name' => $databaseName,
                'language' => $language,
                'color' => '#FF981B',
                'imported' => true,
                'enabled' => true
            ]);
        }

        $index = 0;
        DB::beginTransaction();

        $handle = fopen(Storage::path('dictionaries/' . $fileName), "r");
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

            DB::table($databaseName)->insert([
                'word' => mb_strtolower($data[0], 'UTF-8'),
                'definitions' => mb_strtolower($data[1], 'UTF-8')
            ]);

            if ($index % 1000 == 0) {
                DB::commit();
                DB::beginTransaction();
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
    public function importWiktionary($name, $language, $fileName, $databaseName) {
        // create dictionary table 
        Schema::dropIfExists($databaseName);
        Schema::create($databaseName, function (Blueprint $table) {
            $table->id();
            $table->string('word', 256)->collation('utf8mb4_bin')->index();
            $table->string('definitions', 256)->collation('utf8mb4_bin');
            $table->timestamps();
        });

        // add dictionary to the dictionaries table
        $dictionary = DB::table('dictionaries')->where('name', $name)->first();
        if (!$dictionary) {
            DB::table('dictionaries')->insert([
                'name' => $name,
                'database_table_name' => $databaseName,
                'language' => $language,
                'color' => '#E9CDA0',
                'imported' => true,
                'enabled' => true
            ]);
        }

        $index = 0;
        DB::beginTransaction();
        $handle = fopen(Storage::path('dictionaries/' . $fileName), "r");
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

            DB::table($databaseName)->insert([
                'word' => $word,
                'definitions' => $filteredDefinitions
            ]);

            if ($index % 1000 == 0) {
                DB::commit();
                DB::beginTransaction();
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
        $file = fopen(base_path() . '/storage/app/dictionaries/radicals.txt', 'r');
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
        $radicalStrokesFiles = fopen(base_path() . '/storage/app/dictionaries/radical-strokes.txt', 'r');
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
        $reader->open(base_path() . '/storage/app/dictionaries/kanjidic2.xml');
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
    public function jmdictImport() {
        $file = fopen(base_path() . '/storage/app/dictionaries/jmdict_processed.txt', 'r');
        DB::statement('DELETE FROM dict_jp_jmdict');
        DB::statement('DELETE FROM dict_jp_jmdict_words');
        DB::statement('DELETE FROM dict_jp_jmdict_readings');

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
            }
            
            $index ++;
        }   
        
        DB::commit();
        fclose($file);
        DB::table('dictionaries')->where('database_table_name', 'dict_jp_jmdict')->update(['enabled' => true]);
    }

    /* 
        Converts jmdict to text. Should be moved to python.
    */
    public function jmdictXmlToText() {
        $file = fopen(base_path() . '/storage/app/dictionaries/jmdict.txt', 'w');
        $doc = new \DOMDocument();
        $reader = new \XMLReader();
        $reader->open(base_path() . '/storage/app/dictionaries/JMdict_e.xml');
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
}