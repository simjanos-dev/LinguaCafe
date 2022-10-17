<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lesson;
use App\Models\Vocabulary;
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
            $node = simplexml_import_dom($doc->importNode($reader->expand(), true));
            
            // get word and reading
            if (isset($node->k_ele)) {
                $entry->word = $node->k_ele[0]->keb->__toString();
            } else if (isset($node->r_ele)) {
                $entry->word = $node->r_ele[0]->reb->__toString();
            }
            
            if (isset($node->k_ele)) {
                $entry->reading = $node->r_ele[0]->reb->__toString();
            }

            // get word translation and pos
            $entry->translation = '';
            $entry->pos = '';
            for ($i = 0; $i < count($node->sense); $i++) {
                // translation
                for ($j = 0; $j < count($node->sense[$i]->gloss); $j++) {
                    if (strlen($entry->translation)) {
                        $entry->translation .= ';';
                    }

                    $entry->translation .= $node->sense[$i]->gloss[$j];
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


            fwrite($file, $entry->word . '|' . $entry->pos . '|' . $entry->translation . "\r\n");
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
        
        Vocabulary::where('dictionary_name', 'jmdict')->delete();

        $index = 0;
        DB::beginTransaction();
        while (($line = fgets($file)) !== false) {

            $data = explode('|', $line);
            
            $vocabulary = new Vocabulary();
            $vocabulary->dictionary_name = 'jmdict';
            $vocabulary->word = $data[0];
            $vocabulary->language = 'japanese';
            $vocabulary->translation = $data[1];

            if (mb_strlen($data[2]) > 2) {
                $vocabulary->conjugations = $data[2];
            } else {
                $vocabulary->conjugations = '';
            }

            if ($index % 1000 == 0) {
                echo($index . " ");
                echo str_pad('',4096);
            }

            $vocabulary->save();
            $index ++;
        }    
        DB::commit();

        fclose($file);
        echo('finished');
        ob_implicit_flush(false);
    }
}
