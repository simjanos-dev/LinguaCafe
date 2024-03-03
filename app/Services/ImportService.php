<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\TextBlock;
use App\Models\Lesson;
use App\Models\Book;

class ImportService {

    // stores the python service container's name
    private $pythonService = '';

    public function __construct() {
        $this->pythonService = env('PYTHON_CONTAINER_NAME', 'linguacafe-python-service');
    }
    
    public function importBook($chunkSize, $textProcessingMethod, $file, $bookId, $bookName, $chapterName) {
        DB::disableQueryLog();
        $userId = Auth::user()->id;
        $selectedLanguage = Auth::user()->selected_language;

        // tokenize book
        $text = Http::post($this->pythonService . ':8678/tokenizer/import-book', [
            'language' => $selectedLanguage,
            'textProcessingMethod' => $textProcessingMethod,
            'importFile' => $file,
            'chunkSize' => $chunkSize
        ]);
        
        // get text and token chunks
        $text = json_decode($text);
        $processedChunks = $text->processedChunks;
        $textChunks = $text->textChunks;

        // import chunks
        $this->importChunks($processedChunks, $textChunks, $userId, $selectedLanguage, $bookName, $bookId, $chapterName);        

        return 'success';
    }

    public function importText($chunkSize, $textProcessingMethod, $importText, $bookId, $bookName, $chapterName) {
        DB::disableQueryLog();
        $userId = Auth::user()->id;
        $selectedLanguage = Auth::user()->selected_language;

        // tokenize book
        $text = Http::post($this->pythonService . ':8678/tokenizer/import-text', [
            'language' => $selectedLanguage,
            'textProcessingMethod' => $textProcessingMethod,
            'importText' => $importText,
            'chunkSize' => $chunkSize
        ]);
        
        // get text and token chunks
        $text = json_decode($text);
        $processedChunks = $text->processedChunks;
        $rawChunks = $text->textChunks;

        // import chunks
        $this->importChunks($processedChunks, $rawChunks, $userId, $selectedLanguage, $bookName, $bookId, $chapterName);        

        return 'success';
    }

    public function importSubtitles($chunkSize, $textProcessingMethod, $importSubtitles, $bookId, $bookName, $chapterName) {
        DB::disableQueryLog();
        $userId = Auth::user()->id;
        $selectedLanguage = Auth::user()->selected_language;

        // tokenize book
        $subtitles = Http::post($this->pythonService . ':8678/tokenizer/import-subtitles', [
            'language' => $selectedLanguage,
            'textProcessingMethod' => $textProcessingMethod,
            'importSubtitles' => $importSubtitles,
            'chunkSize' => $chunkSize
        ]);
        
        // get text and token chunks
        $subtitles = json_decode($subtitles);
        $processedChunks = $subtitles->processedChunks;
        $rawChunks = $subtitles->textChunks;
        $subtitleTimestamps = $subtitles->timestamps;

        // import chunks
        $this->importChunks($processedChunks, $rawChunks, $userId, $selectedLanguage, $bookName, $bookId, $chapterName, $subtitleTimestamps);

        return 'success';
    }

    /*
    
        Imports chunks fo raw and tokenized texts. This function
        is used by other import functions to avoid code dupication.
    */
    private function importChunks($processedChunks, $rawChunks, $userId, $selectedLanguage, $bookName, $bookId, $chapterName, $timestamps = null) {
        // retrieve or create book
        if ($bookId == -1) {
            $book = new Book();
            $book->user_id = $userId;
            $book->cover_image = 'default.jpg';
            $book->language = $selectedLanguage;
            $book->name = $bookName;
            $book->save();
        } else {
            $book = Book
                ::where('user_id', $userId)
                ->where('id', $bookId)
                ->first();
            
            if (!$book) {
                return 'error';
            }
        }

        // import each chunk as a chapter
        foreach ($processedChunks as $chunkIndex => $chunk) {
            $chapterNameCalculated = count($processedChunks) > 1 ? $chapterName . ' ' . ($chunkIndex + 1) : $chapterName;

            $lesson = new Lesson();
            $lesson->user_id = $userId;
            $lesson->name = $chapterNameCalculated;
            $lesson->read_count = 0;
            $lesson->word_count = 0;
            $lesson->book_id = $book->id;
            $lesson->language = $selectedLanguage;
            $lesson->raw_text = $rawChunks[$chunkIndex];
            $lesson->unique_words = '';
            $lesson->setProcessedText([]);

            if ($timestamps == null) {
                $lesson->type = 'text';
                $lesson->subtitle_timestamps = '';
            } else {
                $lesson->type = 'subtitle';
                $lesson->subtitle_timestamps = json_encode($timestamps[$chunkIndex]);
            }

            $textBlock = new TextBlock();
            $textBlock->tokenizedWords = $chunk;
            $textBlock->processTokenizedWords();
            $textBlock->collectUniqueWords();
            $textBlock->updateAllPhraseIds();
            $textBlock->createNewEncounteredWords();
            
            $uniqueWordIds = DB
                ::table('encountered_words')
                ->select('id')
                ->where('user_id', Auth::user()->id)
                ->where('language', $selectedLanguage)
                ->whereIn('word', $textBlock->uniqueWords)
                ->pluck('id')
                ->toArray();

            // update lesson word data
            $lesson->setProcessedText($textBlock->processedWords);
            $lesson->word_count = $textBlock->getWordCount();
            $lesson->unique_words = json_encode($textBlock->uniqueWords);
            $lesson->unique_word_ids = json_encode($uniqueWordIds);
            $lesson->save();
        }
    }

    public function getYoutubeSubtitles($url) {
        $subtitleList = Http::post($this->pythonService . ':8678/tokenizer/get-youtube-subtitle-list', [
            'url' => $url,
        ]);
        
        return $subtitleList;
    }

    public function getSubtitleFileContent($fileName) {
        $subtitleContent = Http::post($this->pythonService . ':8678/tokenizer/get-subtitle-file-content', [
            'fileName' => $fileName,
        ]);
        
        return $subtitleContent;
    }
}