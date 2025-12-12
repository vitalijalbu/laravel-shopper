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
        Schema::create('globals', function (Blueprint $table) {
            $table->id();
            $table->string('handle')->unique()->comment('Identificatore univoco per il global set');
            $table->string('title')->comment('Nome visualizzato del global set');
            $table->json('data')->nullable()->comment('Dati del global set in formato JSON');
            $table->timestamps();

            $table->index('handle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('globals');
    }
};
