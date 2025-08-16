<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('handle')->unique(); // hero, featured-products, testimonials
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('component_path'); // Path to Vue/Blade component
            $table->jsonb('schema')->nullable(); // Section schema (settings definition)
            $table->jsonb('preset_data')->nullable(); // Default data for new instances
            $table->jsonb('blocks')->nullable(); // Block definitions
            $table->string('category')->default('content'); // content, media, layout, custom
            $table->boolean('is_global')->default(false); // Can be used across all templates
            $table->boolean('is_active')->default(true);
            $table->integer('max_blocks')->default(50);
            $table->string('icon')->nullable(); // For admin interface
            $table->timestamps();

            // Indexes
            $table->index(['site_id', 'category', 'is_active']);
            $table->index(['is_global', 'is_active']);
            $table->index('handle');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
