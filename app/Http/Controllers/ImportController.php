<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\TextBlock;
use App\Models\Chapter;
use App\Models\Book;


class ImportController extends Controller
{

    public function import(Request $request) {
        $userId = Auth::user()->id;
        $importType = $request->post('importType');
        $importFile = $request->file('importFile');
        $textProcessingMethod = $request->post('textProcessingMethod');
        $bookId = $request->post('bookId');
        $bookName = $request->post('bookName');
        $chapterName = $request->post('chapterName');

        // move file to temp folder
        if ($importType === 'e-book') {
            $randomString = bin2hex(openssl_random_pseudo_bytes(30));
            $extension = '.' . $importFile->getClientOriginalExtension();
            $fileName = $userId . '_' . $randomString . $extension;
            $importFile->move(storage_path('app/temp'), $fileName);
        }

        // import text
        try {
            $this->importBook($textProcessingMethod, storage_path('app/temp') . '/' . $fileName, $bookId, $bookName, $chapterName);
        } catch (\Exception $exception) {
            File::delete(storage_path('app/temp') . '/' . $fileName);
            return 'error';
        }

        // delete temp file
        if ($importType === 'e-book') {
            File::delete(storage_path('app/temp') . '/' . $fileName);
        }

        return 'success';
    }

    private function importBook($textProcessingMethod, $file, $bookId, $bookName, $chapterName) {
        DB::disableQueryLog();
        $userId = Auth::user()->id;
        $selectedLanguage = Auth::user()->selected_language;

        // tokenize book
        $text = Http::post('linguacafe-python-service:8678/tokenizer/import', [
            'language' => $selectedLanguage,
            'textProcessingMethod' => $textProcessingMethod,
            'importFile' => $file,
            'chunkSize' => 9000
        ]);
        
        // get text and token chunks
        $text = json_decode($text);
        $chunks = $text->processedChunks;
        $textChunks = $text->textChunks;

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
            $chapter = new Chapter();
            $chapter->user_id = $userId;
            $chapter->name = $chapterName . ' ' . ($chunkIndex + 1);
            $chapter->read_count = 0;
            $chapter->word_count = 0;
            $chapter->book_id = $book->id;
            $chapter->language = $selectedLanguage;
            $chapter->raw_text = $textChunks[$chunkIndex];
            $chapter->unique_words = '';
            $chapter->setProcessedText([]);

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

            // update chapter word data
            $chapter->setProcessedText($textBlock->processedWords);
            $chapter->word_count = $textBlock->getWordCount();
            $chapter->unique_words = json_encode($textBlock->uniqueWords);
            $chapter->unique_word_ids = json_encode($uniqueWordIds);
            $chapter->save();
        }

        return 'success';
    }
}
