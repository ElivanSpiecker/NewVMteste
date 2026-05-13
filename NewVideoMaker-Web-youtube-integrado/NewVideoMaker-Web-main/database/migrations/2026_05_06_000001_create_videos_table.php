<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('tema');
            $table->integer('duracao')->default(30); // segundos
            $table->enum('status', [
                'pending',
                'generating_script',
                'generating_images',
                'generating_narration',
                'generating_music',
                'generating_subtitles',
                'assembling',
                'done',
                'failed',
            ])->default('pending');
            $table->integer('progresso')->default(0); // 0-100
            $table->text('erro')->nullable();
            $table->string('video_path')->nullable();
            $table->string('srt_path')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
