<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Phrase;

/*
    This class contains functions to transfrom plain text
    into format that can be handled by TextBlockGroup vue 
    component or saved to the database.

    Example for function order to turn raw text into interactive text:
    $textBlock = new TextBlock();
    $textBlock->rawText = $rawText;
    $textBlock->tokenizeRawText();
    $textBlock->processTokenizedWords();
    $textBlock->updateAllPhraseIds();
    $textBlock->collectUniqueWords();
    $textBlock->createNewEncounteredWords();
    $textBlock->prepareTextForReader();
    $textBlock->indexPhrases();
    $textBlockDataForVueComponent = $textBlock->getReaderData();
*/
class TextBlock
{
    use HasFactory;

    public $language = '';

    /*
        This variable contains raw untokenized text. 
    */
    public $rawText = "";

    /*
        This variable contains unprocessed tokenized words coming from 
        python tokenizer service. It will require further processing
        by processTokenizedWords() function before it can be saved into
        the database.
    */
    public $tokenizedWords = [];

    /*
        This variable contains words after they were processed by
        processTokenizedWords() function. This function mostly just
        combines multiple tokens into one in specific languages
        like japanese where the tokenizer doesn't separate 
        words as expected.
    */
    public $processedWords = [];

    /*
        These variables are in a form required by TextBlockGroup vue 
        component. They are created by prepareTextForReader() function,
        and can be directly passed through as props for the TextBlockGroup 
        component to be displayed as interactive text. They can be 
        retrieved as on object by getReaderData() function.
    */
    public $words = [];
    public $uniqueWords = [];
    public $phrases = [];

    function __construct() {
        $this->language = Auth::user()->selected_language;
    }

    /* 
        A setter function for $processedWords. It also checks
        and decodes phrase ids if they are still in json format.
    */
    public function setProcessedWords($processedWords) {
        $this->processedWords = $processedWords;

        if (gettype($processedWords[0]->phrase_ids) == 'string') {
            foreach ($this->processedWords as $word) {
                $word->phrase_ids = json_decode($word->phrase_ids);
            }
        }
    }

    /*
        Returns word count excluding words which should
        be skipped (specialc characters mostly).
    */
    public function getWordCount() {
        $wordsToSkip = config('langapp.wordsToSkip');        
        $wordCount = 0;
        foreach ($this->processedWords as $word) {
            if (!in_array($word->word, $wordsToSkip, true)) {
                $wordCount ++;
            }
        }

        return $wordCount;
    }

    /* 
        Sends the raw text to python tokenizer service, and stores the result.
    */
    public function tokenizeRawText() {
        $this->tokenizedWords = Http::post('langapp-python-service-dev:8678/tokenizer/', [
            'raw_text' => preg_replace("/ {2,}/", " ", str_replace(["\r\n", "\r", "\n"], " NEWLINE ", $this->rawText)),
        ]);

        $this->tokenizedWords = json_decode($this->tokenizedWords);
    }
    
    /* 
        Loops through the list of words returned by python tokenizer
        and creates a list of processed words in a format that can 
        be saved into the database. This function can be skipped, if
        data is already coming from database and has already been 
        processed.
    */
    public function processTokenizedWords() {
        $userId = Auth::user()->id;
        $this->processedWords = [];
        $processedWordCount = 0;
        $wordCount = count($this->tokenizedWords);
        for ($wordIndex = 0; $wordIndex < $wordCount; $wordIndex++) {
            $word = new \stdClass();
            $word->user_id = $userId;
            $word->word_index = $wordIndex;
            $word->sentence_index = $this->tokenizedWords[$wordIndex]->si;
            $word->word = $this->tokenizedWords[$wordIndex]->w;
            $word->reading = $this->tokenizedWords[$wordIndex]->r;
            $word->lemma = $this->tokenizedWords[$wordIndex]->l;
            $word->lemma_reading = $this->tokenizedWords[$wordIndex]->lr;
            $word->pos = $this->tokenizedWords[$wordIndex]->pos;
            $word->phrase_ids = [];

            if ($wordIndex < $wordCount - 1 && $this->tokenizedWords[$wordIndex]->pos == 'VERB' && $this->tokenizedWords[$wordIndex + 1]->pos == 'VERB') {
                $wordIndex ++;
                $word->word .= $this->tokenizedWords[$wordIndex]->w;
                $word->reading .= $this->tokenizedWords[$wordIndex]->r;
                $word->lemma_reading = $this->tokenizedWords[$wordIndex - 1]->r . $this->tokenizedWords[$wordIndex]->lr;
                $word->lemma = $this->tokenizedWords[$wordIndex - 1]->w . $this->tokenizedWords[$wordIndex]->l;
            }
            
            if ($this->tokenizedWords[$wordIndex]->pos == 'VERB' && $this->tokenizedWords[$wordIndex]->w !== $this->tokenizedWords[$wordIndex]->l && $wordIndex < $wordCount - 1 && $this->tokenizedWords[$wordIndex + 1]->pos == 'AUX') {
                do {
                    $wordIndex ++;
                    if ($this->tokenizedWords[$wordIndex]->pos == 'AUX') {
                        $word->word .= $this->tokenizedWords[$wordIndex]->w;
                        $word->reading .= $this->tokenizedWords[$wordIndex]->r;
                    } else {
                        $wordIndex --; break;
                    }
                } while($this->tokenizedWords[$wordIndex]->pos == 'AUX' && $wordIndex < $wordCount - 1);
            } else if ($this->tokenizedWords[$wordIndex]->pos == 'VERB' && $this->tokenizedWords[$wordIndex]->w !== $this->tokenizedWords[$wordIndex]->l && $wordIndex < $wordCount - 1 && $this->tokenizedWords[$wordIndex + 1]->pos == 'SCONJ') {
                do {
                    $wordIndex ++;
                    if ($this->tokenizedWords[$wordIndex]->pos == 'SCONJ') {
                        $word->word .= $this->tokenizedWords[$wordIndex]->w;
                        $word->reading .= $this->tokenizedWords[$wordIndex]->r;
                    } else {
                        $wordIndex --; break;
                    }
                } while($this->tokenizedWords[$wordIndex]->pos == 'SCONJ' && $wordIndex < $wordCount - 1);
            }

            $this->processedWords[$processedWordCount] = $word;
            $processedWordCount ++;
        }
    }

    /*
        This function creates records in encountered_words 
        database table for each new word that the user 
        encounters for the first time.
    */
    public function createNewEncounteredWords() {
        // a regular expression for japanese kanji characters
        $kanjipattern = "/[a-zA-Z0-9０-９あ-んア-ンー。、:？！＜＞： 「」（）｛｝≪≫〈〉《》【】『』〔〕［］・\n\r\t\s\(\)　]/u";

        $encounteredWords = DB::table('encountered_words')
            ->select('word')
            ->where('user_id', Auth::user()->id)
            ->where('language', $this->language)
            ->whereIn('word', $this->uniqueWords)
            ->get();

        for ($wordIndex = 0; $wordIndex < count($this->processedWords); $wordIndex ++) {
            $word = $this->processedWords[$wordIndex];
            $wordId = $encounteredWords->search(function ($item, $key) use($word) {
                return $item->word == mb_strtolower($word->word);
            });

            if ($wordId === false) {
                if ($this->language == 'japanese') {
                    $kanji = preg_replace($kanjipattern, "", $this->processedWords[$wordIndex]->word);
                    $kanji = preg_split("//u", $kanji, -1, PREG_SPLIT_NO_EMPTY);
                }

                $encounteredWord = new EncounteredWord();
                $encounteredWord->user_id = Auth::user()->id;
                $encounteredWord->language = $this->language;
                $encounteredWord->word = mb_strtolower($this->processedWords[$wordIndex]->word, 'UTF-8');
                $encounteredWord->lemma = $this->processedWords[$wordIndex]->lemma;
                $encounteredWord->base_word = $this->processedWords[$wordIndex]->lemma;
                $encounteredWord->kanji = $this->language == 'japanese' ? implode('', $kanji) : '';
                $encounteredWord->reading = $this->processedWords[$wordIndex]->reading;
                $encounteredWord->base_word_reading = $this->processedWords[$wordIndex]->lemma_reading;
                $encounteredWord->example_sentence = '';
                $encounteredWord->stage = 2;
                $encounteredWord->translation = '';

                if ($encounteredWord->base_word == $encounteredWord->word) {
                    $encounteredWord->base_word = '';
                    $encounteredWord->base_word_reading = '';
                }

                $encounteredWord->save();
            }
        }
    }

    public function collectUniqueWords() {
        $this->uniqueWords = [];
        for ($wordIndex = 0; $wordIndex < count($this->processedWords); $wordIndex ++) {
            if (!in_array(mb_strtolower($this->processedWords[$wordIndex]->word), $this->uniqueWords, true)) {
                array_push($this->uniqueWords, mb_strtolower($this->processedWords[$wordIndex]->word, 'UTF-8'));
            }
        }
    }

    function updateAllPhraseIds() {
        $phrases = Phrase
            ::where('user_id', Auth::user()->id)
            ->where('language', $this->language)
            ->get();
        
        foreach($phrases as $phrase) {
            $this->updatePhraseIds($phrase);
        }
    }

    /* 
        This function loops through the words of the TextBlock
        and tags them if they are part of the phrase given
        as an argument.
    */
    function updatePhraseIds($phrase) {
        // decode phrase words array
        if (gettype($phrase->words) == 'string') {
            $phrase->words = json_decode($phrase->words);
        }

        // check if the lesson contains the phrase
        // otherwise skip the algorithm. 
        foreach ($phrase->words as $phraseWord) {
            if (!in_array($phraseWord, $this->uniqueWords, true)) {
                return false;
            }
        }
        
        $phraseLength = count($phrase->words);
        $phraseOccurences = [];
        foreach($this->processedWords as $wordIndex => $word) {
            $lowercaseWord = mb_strtolower($word->word, 'UTF-8');
            
            // Check if the current word is the start of the phrase.
            if ($lowercaseWord == $phrase->words[0]) {
                $phraseOccurence = new \stdClass();
                $phraseOccurence->word = $lowercaseWord;
                $phraseOccurence->wordIndex = $wordIndex;
                $phraseOccurence->newLineCount = 0;
                array_push($phraseOccurences, array($phraseOccurence));
            }

            // Check if the current word is the continuation of a phrase.
            for ($p = 0 ; $p < count($phraseOccurences); $p++) {
                $phraseOccurenceLength = count($phraseOccurences[$p]);
                
                // If the phrase occurance length equals with phrase length
                // then it means it's an exact match match. There is no need 
                // for further comparison, so the loop can be skipped.
                if ($phraseOccurenceLength === $phraseLength) {
                    continue;
                }

                if ($wordIndex - 1 === $phraseOccurences[$p][$phraseOccurenceLength - 1]->wordIndex + $phraseOccurences[$p][$phraseOccurenceLength - 1]->newLineCount 
                    && $phrase->words[$phraseOccurenceLength] === $lowercaseWord) {
                    
                    $phraseOccurence = new \stdClass();
                    $phraseOccurence->word = $lowercaseWord;
                    $phraseOccurence->wordIndex = $wordIndex;
                    $phraseOccurence->newLineCount = 0;
                    array_push($phraseOccurences[$p], $phraseOccurence);
                }
 
                // Count 'NEWLINE' words. This is needed because phrases doesn't 
                // have them, so it must be skipped when comparing them with text. 
                if ($word->word === 'NEWLINE') {
                    $phraseOccurences[$p][$phraseOccurenceLength - 1]->newLineCount ++;
                }
            }
        }
        
        // Mark all instance of the phrase in text.
        for ($p = 0 ; $p < count($phraseOccurences); $p++) {
            // Skip partial phrase matches. 
            if (count($phraseOccurences[$p]) < count($phrase->words)) {
                continue;
            }

            for ($i = 0; $i < count($phraseOccurences[$p]); $i++) {
                $tempArray = $this->processedWords[$phraseOccurences[$p][$i]->wordIndex]->phrase_ids;
                array_push($tempArray, $phrase->id);
                $this->processedWords[$phraseOccurences[$p][$i]->wordIndex]->phrase_ids = $tempArray;
            }
        }
    }
    
    /*
        Collects all phrases in the text, then replaces 
        phrase_ids with phraseIndexes. This is required
        for TextBlock vue object for better search speeds.
    */
    public function indexPhrases() {
        // get unique phrase ids
        $phraseIds = [];
        for ($wordIndex = 0; $wordIndex < count($this->words); $wordIndex ++) {
            for ($phraseCounter = 0; $phraseCounter < count($this->words[$wordIndex]->phrase_ids); $phraseCounter ++) {
                if (!in_array($this->words[$wordIndex]->phrase_ids[$phraseCounter], $phraseIds)) {
                    array_push($phraseIds, $this->words[$wordIndex]->phrase_ids[$phraseCounter]);
                }
            }
        }
        
        sort($phraseIds);

        $this->phrases = Phrase
            ::where('user_id', Auth::user()->id)
            ->where('language', $this->language)
            ->whereIn('id', $phraseIds)
            ->orderBy('id')
            ->get();

        for ($phraseIndex = 0; $phraseIndex < count($this->phrases); $phraseIndex++) {
            $this->phrases[$phraseIndex]->words = json_decode($this->phrases[$phraseIndex]->words);
        }

        for ($wordIndex = 0; $wordIndex < count($this->words); $wordIndex ++) {
            foreach($this->words[$wordIndex]->phrase_ids as $phraseId) {
                $index = array_search($phraseId, $phraseIds);
                $tempArray = $this->words[$wordIndex]->phraseIndexes;
                array_push($tempArray, $index);
                $this->words[$wordIndex]->phraseIndexes = $tempArray;
            }
        }
    }

    /*
        This function adds additional variables for words
        which are required for TextBlockGroup vue component 
        to work.
    */
    public function prepareTextForReader() {
        $this->words = [];
        $encounteredWords = DB::table('encountered_words')
            ->where('user_id', Auth::user()->id)
            ->where('language', $this->language)
            ->whereIn('word', $this->uniqueWords)
            ->get();

        for ($wordIndex = 0; $wordIndex < count($this->processedWords); $wordIndex ++) {
            // make the word into an object
            $word = $this->processedWords[$wordIndex];
            $word->selected = false;
            $word->hover = false;
            $word->phraseStage = 'learning';
            $word->phraseStart = false;
            $word->phraseEnd = false;
            $word->phraseIndexes = [];

            $wordId = $encounteredWords->search(function ($item, $key) use($word) {
                return $item->word == mb_strtolower($word->word);
            });

            $word->stage = $encounteredWords[$wordId]->stage;
            $word->lookup_count = $encounteredWords[$wordId]->lookup_count;

            $this->words[] = $word;
        }

        $this->uniqueWords = $encounteredWords;
    }

    /*
        This function returns an object that only
        contains variables which are required by
        TextBlockGroup vue component.
    */
    public function getReaderData() {
        $textBlock = new \stdClass();
        $textBlock->words = $this->words;
        $textBlock->uniqueWords = $this->uniqueWords;
        $textBlock->phrases = $this->phrases;
        return $textBlock;
    }
}
