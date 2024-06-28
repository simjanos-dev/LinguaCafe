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
        
        $chunks = json_decode($text);

        // import chunks
        $this->importChunks($chunks, $userId, $selectedLanguage, $bookName, $bookId, $chapterName);        
        
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

        // import subtitles
        $subtitles = Http::post($this->pythonService . ':8678/tokenizer/import-subtitles', [
            'language' => $selectedLanguage,
            'subtitles' => $importSubtitles,
            'chunkSize' => $chunkSize
        ]);
        
        // get text and token chunks
        $chunks = json_decode($subtitles);

        // import chunks
        $this->importChunks($chunks, $userId, $selectedLanguage, $bookName, $bookId, $chapterName, true);
    }

    /*
    
        Imports chunks fo raw and tokenized texts. This function
        is used by other import functions to avoid code dupication.
    */
    private function importChunks($chunks, $userId, $selectedLanguage, $bookName, $bookId, $chapterName, $isSubtitle = false) {
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
        foreach ($chunks as $chunkIndex => $chunk) {
            $chapterNameCalculated = count($chunks) > 1 ? $chapterName . ' ' . ($chunkIndex + 1) : $chapterName;

            $chapter = new Chapter();
            $chapter->user_id = $userId;
            $chapter->name = $chapterNameCalculated;
            $chapter->is_processed = false;
            $chapter->read_count = 0;
            $chapter->word_count = 0;
            $chapter->book_id = $book->id;
            $chapter->language = $selectedLanguage;
            $chapter->unique_words = '';
            $chapter->subtitle_timestamps = '';
            $chapter->type = $isSubtitle ? 'subtitle' : 'text';
            $chapter->raw_text = $isSubtitle ? json_encode($chunk) : $chunk;
            $chapter->save();
            
            \App\Jobs\ProcessChapter::dispatch($userId, $chapter->id);
        }

        return true;
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