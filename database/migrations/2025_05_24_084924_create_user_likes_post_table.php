<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('user_likes_post', function (Blueprint $table) {
    // Link to the users table
    $table->foreignId('user_id')
          ->constrained()
          ->cascadeOnDelete();

    // Link to the posts table
    $table->foreignId('post_id')
          ->constrained()
          ->cascadeOnDelete();

    // Record when the like happened
    $table->timestamps();

    // Prevent duplicate likes
    $table->primary(['user_id', 'post_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_likes_post');
    }
};
