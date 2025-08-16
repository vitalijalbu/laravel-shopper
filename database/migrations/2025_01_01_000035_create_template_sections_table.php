<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('templates')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->jsonb('settings')->nullable(); // Instance-specific settings
            $table->jsonb('blocks_data')->nullable(); // Block instances with data
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->string('section_key')->nullable(); // Unique key for this section instance
            $table->timestamps();

            // Indexes
            $table->index(['template_id', 'sort_order']);
            $table->index(['section_id', 'is_visible']);
            $table->unique(['template_id', 'section_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_sections');
    }
};
