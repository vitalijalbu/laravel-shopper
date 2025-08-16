<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('handle')->index(); // index, product, collection, page, etc.
            $table->string('name');
            $table->string('type')->default('page'); // page, product, collection, blog, article
            $table->jsonb('sections')->nullable(); // Array of section configurations
            $table->jsonb('settings')->nullable(); // Template-level settings
            $table->string('layout')->default('theme'); // Layout file to use
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Indexes for performance
            $table->unique(['site_id', 'handle']);
            $table->index(['site_id', 'type', 'is_active']);
            $table->index(['type', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
