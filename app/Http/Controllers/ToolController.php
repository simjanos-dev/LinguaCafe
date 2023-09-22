<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kanji;
use App\Models\Radical;
use App\Models\Lesson;
use App\Models\VocabularyJmdict;
use App\Models\VocabularyJmdictWord;
use App\Models\VocabularyJmdictReading;
use Illuminate\Support\Facades\DB;

class ToolController extends Controller
{
    public function jmdictTextGenerator() {
        ob_implicit_flush(true);
        $file = fopen(base_path() . '/storage/app/dictionaries/jmdict.txt', 'w');
        $doc = new \DOMDocument();
        $reader = new \XMLReader();
        $reader->open(base_path() . '/storage/app/dictionaries/JMdict_e.xml');
        $index = 0;
        

        while ($reader->read() && $reader->name !== 'entry');
        while ($reader->name === 'entry') {
            if ($index % 1000 == 0) {
                echo($index . " ");
                echo str_pad('',4096);
            }

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
        ob_implicit_flush(false);
    }

    public function kanjiRadicalImport() {
        // init
        DB::beginTransaction();
        $file = fopen(base_path() . '/storage/app/dictionaries/radicals.txt', 'r');
        $index = 0;
        ob_implicit_flush(true);

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
            //乞 has no character, image must be displayed
        ];


        // delete old database entries
        DB::statement('DELETE FROM dictionary_ja_kanji_radicals');
        
        // load radical stroke counts into an array
        $radicalStrokesFiles = fopen(base_path() . '/storage/app/dictionaries/radical-strokes.txt', 'r');
        $radicalStrokeCountsData = [];

        while (($line = fgets($radicalStrokesFiles)) !== false) {
            $data = explode(' ', $line);
            $radicalStrokeCountsData[$data[0]] = $data[1];
        }

        // loop through the radicals files
        while (($line = fgets($file)) !== false) {

            // display feedback for user
            if ($index > 0 && $index % 100 == 0) {
                echo($index . " ");
                echo str_pad('',4096);
            }

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
        ob_implicit_flush(false);
        echo('finished');
    }

    public function kanjiImport() {
        $jlpt = [
            '1' => 1,
            '2' => 2,
            '3' => 4,
            '4' => 5,
        ];

        DB::statement('DELETE FROM dictionary_ja_kanji');

        ob_implicit_flush(true);
        $doc = new \DOMDocument();
        $reader = new \XMLReader();
        $reader->open(base_path() . '/storage/app/dictionaries/kanjidic2.xml');
        $index = 0;        

        DB::beginTransaction();
        while ($reader->read() && $reader->name !== 'character');
        while ($reader->name === 'character') {
            if ($index % 100 == 0) {
                echo($index . " ");
                echo str_pad('',4096);
            }

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
        echo('finished');
        ob_implicit_flush(false);
    }

    public function jmdictImport() {
        
        ob_implicit_flush(true);
        $file = fopen(base_path() . '/storage/app/dictionaries/jmdict_processed.txt', 'r');
        DB::statement('DELETE FROM dictionary_ja_jmdict');
        DB::statement('DELETE FROM dictionary_ja_jmdict_words');
        DB::statement('DELETE FROM dictionary_ja_jmdict_readings');                

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
                $jmdictWord->dictionary_ja_jmdict_id = $vocabulary->id;
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
                $jmdictReading->dictionary_ja_jmdict_id = $vocabulary->id;
                $jmdictReading->save();
            }

            
            
            if ($index % 1000 == 0) {
                echo($index . " ");
                echo str_pad('',4096);
            }
            
            $index ++;
        }   
        
        DB::commit();

        fclose($file);
        echo('finished');
        ob_implicit_flush(false);
    }
}
