<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('imagens_modo', 16)->default('gerar')->after('duracao');
            $table->string('narracao_modo', 16)->default('gerar')->after('imagens_modo');
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['imagens_modo', 'narracao_modo']);
        });
    }
};
