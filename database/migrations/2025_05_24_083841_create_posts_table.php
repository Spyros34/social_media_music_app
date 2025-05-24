<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            // Link back to the user who created this post:
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Store the Spotify track data as JSON:
            $table->json('track');

            // Number of likes (default to 0):
            $table->unsignedInteger('likes')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};