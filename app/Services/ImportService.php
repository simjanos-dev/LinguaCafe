<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// modelx
use App\Models\Chapter;
use App\Models\Book;

// services
use App\Services\TextBlockService;

class ImportService {

    // stores the python service container's name
    private $pythonService = '';

    public function __construct() {
        $this->pythonService = env('PYTHON_CONTAINER_NAME', 'linguacafe-python-service');
    }

    public function importBook($chunkSize, $eBookChapterSortMethod, $textProcessingMethod, $file, $bookId, $bookName, $chapterName) {
        DB::disableQueryLog();
        $userId = Auth::user()->id;
        $selectedLanguage = Auth::user()->selected_language;

        // tokenize book
        $text = Http::post($this->pythonService . ':8678/tokenizer/import-book', [
            'language' => $selectedLanguage,
            'textProcessingMethod' => $textProcessingMethod,
            'chapterSortMethod' => $eBookChapterSortMethod,
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

            $chapter = new Chapter();
            $chapter->user_id = $userId;
            $chapter->name = $chapterNameCalculated;
            $chapter->read_count = 0;
            $chapter->word_count = 0;
            $chapter->book_id = $book->id;
            $chapter->language = $selectedLanguage;
            $chapter->raw_text = $rawChunks[$chunkIndex];
            $chapter->unique_words = '';
            $chapter->setProcessedText([]);

            if ($timestamps == null) {
                $chapter->type = 'text';
                $chapter->subtitle_timestamps = '';
            } else {
                $chapter->type = 'subtitle';
                $chapter->subtitle_timestamps = json_encode($timestamps[$chunkIndex]);
            }

            $textBlock = new TextBlockService();
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

            // update chapter word data
            $chapter->setProcessedText($textBlock->processedWords);
            $chapter->word_count = $textBlock->getWordCount();
            $chapter->unique_words = json_encode($textBlock->uniqueWords);
            $chapter->unique_word_ids = json_encode($uniqueWordIds);
            $chapter->save();
        }

        // update book words
        (new BookService())->updateBookWordCount($userId, $book->id);
    }

    public function getYoutubeSubtitles($url) {
        $subtitleList = Http::post($this->pythonService . ':8678/tokenizer/get-youtube-subtitle-list', [
            'url' => $url,
        ]);
        
        return json_decode($subtitleList);
    }

    public function getSubtitleFileContent($fileName) {
        $subtitleContent = Http::post($this->pythonService . ':8678/tokenizer/get-subtitle-file-content', [
            'fileName' => $fileName,
        ]);
        
        return json_decode($subtitleContent);
    }

    public function getWebsiteText($url) {
        $websiteText = Http::post($this->pythonService . ':8678/tokenizer/get-website-text', [
            'url' => $url,
        ]);

        return json_decode($websiteText);
    }
}