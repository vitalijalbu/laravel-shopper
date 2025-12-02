<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('handle')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            // URL Configuration
            $table->string('url')->index();
            $table->string('domain')->nullable()->unique();
            $table->jsonb('domains')->nullable()->comment('Multiple domains for this site');

            // Localization (default locale + supported locales managed via channels)
            $table->string('locale', 10)->index()->comment('Default locale fallback');
            $table->string('lang', 5)->index();

            // Geographic & Market Configuration
            $table->jsonb('countries')->nullable()->comment('Array of ISO country codes for this market');

            // Currency Configuration
            $table->string('default_currency', 3)->default('EUR')->index()->comment('Default currency for this site');

            // Tax Configuration
            $table->boolean('tax_included_in_prices')->default(false)->comment('Prices include tax');
            $table->string('tax_region')->nullable()->index()->comment('Default tax region (e.g., EU, US, UK)');

            // Priority & Status
            $table->integer('priority')->default(0)->index()->comment('Higher priority sites for overlapping domains');
            $table->boolean('is_default')->default(false)->index()->comment('Default site for new sessions');
            $table->string('status')->default('active')->index();
            $table->integer('order')->default(0)->index()->comment('Display order in admin');

            // Publishing
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('unpublished_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->jsonb('attributes')->nullable()->comment('Custom metadata');

            // Indexes
            $table->index(['handle', 'status']);
            $table->index(['locale', 'status']);
            $table->index(['default_currency', 'status']);
            $table->index(['is_default', 'status', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
