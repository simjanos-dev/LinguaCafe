<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChapterReadTrackingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->book = Book::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    public function test_chapters_endpoint_returns_read_count(): void
    {
        Chapter::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'read_count' => 3,
        ]);

        $response = $this->actingAs($this->user)->post('/chapters', [
            'bookId' => $this->book->id,
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('chapters', $data);
        $this->assertCount(1, $data['chapters']);
        $this->assertEquals(3, $data['chapters'][0]['read_count']);
    }

    public function test_chapters_endpoint_returns_updated_at(): void
    {
        Chapter::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->actingAs($this->user)->post('/chapters', [
            'bookId' => $this->book->id,
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('updated_at', $data['chapters'][0]);
        $this->assertNotNull($data['chapters'][0]['updated_at']);
    }

    public function test_finish_chapter_increments_read_count(): void
    {
        $chapter = Chapter::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'read_count' => 0,
        ]);

        $response = $this->actingAs($this->user)->post('/chapters/finish', [
            'chapterId' => $chapter->id,
            'autoMoveWordsToKnown' => false,
            'uniqueWords' => [],
            'autoLevelUpWords' => false,
            'leveledUpWords' => [],
            'leveledUpPhrases' => [],
            'language' => 'english',
        ]);

        $response->assertStatus(200);

        $chapter->refresh();
        $this->assertEquals(1, $chapter->read_count);
    }

    public function test_finish_chapter_updates_timestamp(): void
    {
        $chapter = Chapter::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'read_count' => 0,
            'updated_at' => now()->subDay(),
        ]);

        $originalTimestamp = $chapter->updated_at;

        $this->actingAs($this->user)->post('/chapters/finish', [
            'chapterId' => $chapter->id,
            'autoMoveWordsToKnown' => false,
            'uniqueWords' => [],
            'autoLevelUpWords' => false,
            'leveledUpWords' => [],
            'leveledUpPhrases' => [],
            'language' => 'english',
        ]);

        $chapter->refresh();
        $this->assertTrue($chapter->updated_at->gt($originalTimestamp));
    }
}
