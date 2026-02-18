<?php

namespace App\Services;

use App\Enums\ChapterProcessingStatusEnum;
use App\Enums\Import\EbookChapterSortMethodEnum;
use App\Enums\Import\EbookTextProcessingMethodEnum;
use App\Enums\Import\ImportTypeEnum;
use App\Enums\Import\TokenizerRequestTypeEnum;
use App\Helpers\Language\LanguageConfig;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImportService
{
    private TempFileService $tempFileService;

    private $pythonServiceHost;

    public function __construct()
    {
        $this->tempFileService = new TempFileService;
        $this->pythonServiceHost = env('PYTHON_CONTAINER_NAME', 'linguacafe-python-service');
    }

    public function import(
        ImportTypeEnum $importType,
        User $user,
        LanguageConfig $language,
        int $chunkSize,
        EbookChapterSortMethodEnum $eBookChapterSortMethod,
        EbookTextProcessingMethodEnum $eBookTextProcessingMethod,
        string $chapterName,
        ?Book $book,
        ?string $bookName,
        ?UploadedFile $importFile,
        ?string $importText,
        ?string $importSubtitles,
    ): void {
        if ($book && $book->user_id !== $user->id) {
            throw new \Exception('Book not found or unauthorized.');
        }

        $fileName = $importFile ? $this->tempFileService->moveFileToTempFolder($user, $importFile) : null;
        try {
            DB::disableQueryLog();

            $requestUrl = $this->getImportRequestUrl($importType);
            $requestPayload = $this->getImportRequestPayload(
                $importType,
                $language,
                $chunkSize,
                $eBookChapterSortMethod,
                $eBookTextProcessingMethod,
                $this->getFullTempFilePath($fileName),
                $importText ?? null,
                $importSubtitles ?? null
            );

            $text = Http::post($requestUrl, $requestPayload);
            $response = json_decode($text);

            // Grab cover if EPUB
            $coverImageBase64 = null;
            if ($importType === ImportTypeEnum::E_BOOK && is_object($response) && property_exists($response, 'chunks')) {
                $chunks = $response->chunks;
                $coverImageBase64 = $response->coverImage ?? null;
            } else {
                // Handle other import formats
                $chunks = $response;
            }

            $this->importChapters($chunks, $user, $language, $bookName, $book, $chapterName, $importSubtitles !== null, $coverImageBase64);
        } catch (\Exception $e) {
            if ($importFile) {
                $this->tempFileService->deleteTempFile($fileName);
            }

            throw new \Exception($e->getMessage());
        }

        if ($importFile) {
            $this->tempFileService->deleteTempFile($fileName);
        }
    }

    private function getFullTempFilePath(?string $fileName): ?string
    {
        if (!$fileName) {
            return null;
        }

        return storage_path('app/temp') . '/' . $fileName;
    }

    private function getImportRequestUrl(ImportTypeEnum $importType): string
    {
        $tokenizerRequestType = $this->getTokenizerRequestType($importType);

        return match ($tokenizerRequestType) {
            TokenizerRequestTypeEnum::E_BOOK => $this->pythonServiceHost . ':8678/tokenizer/import-book',
            TokenizerRequestTypeEnum::SUBTITLE => $this->pythonServiceHost . ':8678/tokenizer/import-subtitles',
            TokenizerRequestTypeEnum::TEXT => $this->pythonServiceHost . ':8678/tokenizer/cut-and-tokenize-text',
        };
    }

    private function getImportRequestPayload(
        ImportTypeEnum $importType,
        LanguageConfig $language,
        int $chunkSize,
        EbookChapterSortMethodEnum $eBookChapterSortMethod,
        EBookTextProcessingMethodEnum $eBookTextProcessingMethod,
        ?string $fileName,
        ?string $importText,
        ?string $importSubtitles,
    ): array {
        $commonPayloadData = [
            'language' => $language->name,
            'chunkSize' => $chunkSize,
        ];

        $tokenizerRequestType = $this->getTokenizerRequestType($importType);
        $requestTypeBasedImportData = match ($tokenizerRequestType) {
            TokenizerRequestTypeEnum::E_BOOK => [
                'chapterSortMethod' => $eBookChapterSortMethod->value,
                'textProcessingMethod' => $eBookTextProcessingMethod->value,
                'importFile' => $fileName,
            ],
            TokenizerRequestTypeEnum::SUBTITLE => [
                'subtitles' => $importSubtitles,
            ],
            TokenizerRequestTypeEnum::TEXT => [
                'text' => $importText,
            ],
        };

        return [
            ...$commonPayloadData,
            ...$requestTypeBasedImportData,
        ];
    }

    private function getTokenizerRequestType(ImportTypeEnum $importType): TokenizerRequestTypeEnum
    {
        return match ($importType) {
            ImportTypeEnum::E_BOOK => TokenizerRequestTypeEnum::E_BOOK,
            ImportTypeEnum::JELLYFIN_SUBTITLE => TokenizerRequestTypeEnum::SUBTITLE,
            ImportTypeEnum::SUBTITLE_FILE => TokenizerRequestTypeEnum::SUBTITLE,
            ImportTypeEnum::PLAIN_TEXT => TokenizerRequestTypeEnum::TEXT,
            ImportTypeEnum::TEXT_FILE => TokenizerRequestTypeEnum::TEXT,
            ImportTypeEnum::YOUTUBE => TokenizerRequestTypeEnum::TEXT,
            ImportTypeEnum::WEBSITE => TokenizerRequestTypeEnum::TEXT,
        };
    }

    private function importChapters(
        array $chunks,
        User $user,
        LanguageConfig $language,
        ?string $bookName,
        ?Book $book,
        $chapterName,
        $isSubtitle = false,
        ?string $coverImageBase64 = null
    ): void {
        if (!$book) {
            $book = new Book;
            $book->user_id = $user->id;
            $book->cover_image = null;
            $book->language = $language->name;
            $book->name = $bookName;
            $book->save();

            // Save cover image if available
            if ($coverImageBase64) {
                $this->saveCoverImage($book, $coverImageBase64);
            }
        }

        foreach ($chunks as $chunkIndex => $chunk) {
            $chapterNameCalculated = count($chunks) > 1 ? $chapterName . ' ' . ($chunkIndex + 1) : $chapterName;

            $chapter = new Chapter;
            $chapter->user_id = $user->id;
            $chapter->name = $chapterNameCalculated;
            $chapter->processing_status = ChapterProcessingStatusEnum::UNPROCESSED->value;
            $chapter->read_count = 0;
            $chapter->word_count = 0;
            $chapter->book_id = $book->id;
            $chapter->language = $language->name;
            $chapter->unique_words = '';
            $chapter->subtitle_timestamps = '';
            $chapter->type = $isSubtitle ? 'subtitle' : 'text';
            $chapter->raw_text = $isSubtitle ? json_encode($chunk) : $chunk;
            $chapter->save();

            \App\Jobs\ProcessChapter::dispatch($user->id, $user->uuid, $chapter->id, $language->name);
        }
    }

    private function saveCoverImage(Book $book, string $coverImageBase64): void
    {
        try {
            // Decode base64 image
            $imageData = base64_decode($coverImageBase64);
            
            if ($imageData === false) {
                return; // Invalid base64 data, skip saving
            }

            // Detect image format
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageData);
            
            // Set file format
            $extension = match($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                default => 'jpg', // Default to jpg if unknown
            };

            // Generate filename using timestamp (matches existing pattern)
            $timestamp = Carbon::now()->format('YmdHis');
            $fileName = $book->id . '_' . $timestamp . '.' . $extension;
            
            // Save image to storage
            Storage::put('/images/book_images/' . $fileName, $imageData);
            
            // Update book record
            $book->cover_image = $fileName;
            $book->save();
        } catch (\Exception $e) {
            // Log and skip on exception
            \Log::warning("Failed to save cover image for book {$book->id}: " . $e->getMessage());
        }
    }
}
