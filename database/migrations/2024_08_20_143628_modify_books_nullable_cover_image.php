<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Book;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Make `cover_image` nullable
        Schema::table('books', function (Blueprint $table) {
            $table->string('cover_image')->nullable()->change();
        });

        Book::query()->where('cover_image', '=', 'default.jpg')->update(['cover_image' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Book::query()->whereNull('cover_image')->update(['cover_image' => 'default.jpg']);

        // Remove `cover_image` nullability
        Schema::table('books', function (Blueprint $table) {
            $table->string('cover_image')->default('default.jpg')->nullable(false)->change();
        });

    }
};
