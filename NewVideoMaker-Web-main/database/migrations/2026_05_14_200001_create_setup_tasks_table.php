<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setup_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('kind');              // 'install_ollama', 'pull_model', etc.
            $table->string('label');             // o que aparece no UI
            $table->enum('status', ['pending','running','done','failed'])->default('pending');
            $table->integer('progresso')->default(0); // 0-100
            $table->text('mensagem')->nullable();     // ultima mensagem do worker
            $table->text('erro')->nullable();
            $table->timestamps();

            $table->index(['kind','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setup_tasks');
    }
};
