<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lesson;
use App\Models\VocabularyJmdict;
use App\Models\VocabularyJmdictWord;
use App\Models\VocabularyJmdictReading;
use Illuminate\Support\Facades\DB;

class ToolController extends Controller
{
    public function jmdictTextGenerator() {
        ob_implicit_flush(true);
        $file = fopen(base_path() . '/tools/jmdict_text_generator/jmdict.txt', 'w');
        $doc = new \DOMDocument();
        $reader = new \XMLReader();
        $reader->open(base_path() . '/tools/jmdict_text_generator/JMdict_e.xml');
        $index = 0;
        

        while ($reader->read() && $reader->name !== 'entry');
        while ($reader->name === 'entry') {
            if ($index % 1000 == 0) {
                echo($index . " ");
                echo str_pad('',4096);
            }

            $entry = new \StdClass();
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
                    $translation = new \StdClass();
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

    public function jmdictImport() {
        
        ob_implicit_flush(true);
        $file = fopen(base_path() . '/tools/jmdict_conjugation/jmdict_processed.txt', 'r');
        DB::statement('DELETE FROM dictionary_ja_jmdict');
        DB::statement('DELETE FROM dictionary_ja_jmdict_words');
        DB::statement('DELETE FROM dictionary_ja_jmdict_readings');                

        $index = 0;
        DB::beginTransaction();
        while (($line = fgets($file)) !== false) {
            $data = explode('|', $line);
            
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
