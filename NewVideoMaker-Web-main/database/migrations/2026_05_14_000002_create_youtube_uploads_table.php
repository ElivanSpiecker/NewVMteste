<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('youtube_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('youtube_account_id')->constrained('youtube_accounts')->cascadeOnDelete();
            $table->foreignId('video_id')->nullable()->constrained('videos')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('tags')->nullable(); // CSV; YouTube limita 500 chars no total
            $table->string('category_id')->default('22'); // 22 = People & Blogs (default)
            $table->enum('privacy_status', ['private', 'unlisted', 'public'])->default('public');
            $table->boolean('made_for_kids')->default(false);
            $table->timestamp('scheduled_at')->nullable();
            $table->string('source_path'); // caminho absoluto do arquivo MP4 no servidor
            $table->enum('status', [
                'pending',
                'uploading',
                'scheduled',
                'published',
                'failed',
            ])->default('pending');
            $table->integer('progresso')->default(0);
            $table->string('youtube_video_id')->nullable();
            $table->text('erro')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('youtube_uploads');
    }
};
