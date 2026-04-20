<?php

namespace Database\Factories;

use App\Models\Chapter;
use App\Enums\ChapterProcessingStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chapter>
 */
class ChapterFactory extends Factory
{
    protected $model = Chapter::class;

    public function definition(): array
    {
        return [
            'user_id' => 1,
            'book_id' => 1,
            'name' => fake()->sentence(3),
            'read_count' => 0,
            'word_count' => fake()->numberBetween(50, 500),
            'language' => 'english',
            'raw_text' => fake()->paragraph(),
            'processed_text' => '',
            'unique_words' => '[]',
            'unique_word_ids' => '[]',
            'type' => 'text',
            'subtitle_timestamps' => '',
            'processing_status' => ChapterProcessingStatusEnum::PROCESSED->value,
        ];
    }
}
