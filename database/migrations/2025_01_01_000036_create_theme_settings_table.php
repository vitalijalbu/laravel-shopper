<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopper_theme_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('shopper_sites')->cascadeOnDelete();
            $table->string('theme_handle')->default('default');
            $table->jsonb('global_settings')->default('{}'); // Typography, colors, layout
            $table->jsonb('navigation_menus')->default('{}'); // Header, footer, sidebar menus
            $table->jsonb('social_links')->default('{}'); // Social media settings
            $table->jsonb('seo_settings')->default('{}'); // Global SEO settings
            $table->jsonb('custom_css')->default('{}'); // Per-section custom CSS
            $table->jsonb('custom_js')->default('{}'); // Custom JavaScript snippets
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->unique(['site_id', 'theme_handle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopper_theme_settings');
    }
};
