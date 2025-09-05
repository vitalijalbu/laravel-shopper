<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('title');
            $table->string('handle')->index();
            $table->longText('content');
            $table->enum('status', ['published', 'draft', 'private'])->default('draft');
            $table->boolean('show_title')->default(true);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('author_id')->nullable(); // User who created the page
            $table->jsonb('blocks_data')->nullable(); // For page builder blocks
            $table->timestamps();

            // Indexes for performance
            $table->unique(['site_id', 'handle']);
            $table->index(['site_id', 'status']);
            $table->index(['status', 'published_at']);
            $table->index('author_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
