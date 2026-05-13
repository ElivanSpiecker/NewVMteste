<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('youtube_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->string('tags')->nullable();
            $table->string('category_id')->default('22');
            $table->string('privacy_status')->default('private');
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status')->default('scheduled');
            $table->string('youtube_video_id')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('youtube_posts');
    }
};
