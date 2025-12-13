<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Product Options (es: Color, Size, Material)
        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Color, Size, Material, etc.
            $table->string('slug')->unique();
            $table->string('type')->default('select'); // select, swatch, text, radio
            $table->integer('position')->default(0);
            $table->boolean('is_global')->default(true); // Riutilizzabile globalmente
            $table->boolean('is_required')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->boolean('use_for_variants')->default(true); // Used to generate variants
            $table->jsonb('configuration')->nullable(); // Extra config (es: swatch settings)
            $table->timestamps();

            $table->index(['slug', 'is_global']);
            $table->index('position');
        });

        // Product Option Values (es: Red, Blue, XL, Cotton)
        Schema::create('product_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_option_id')->constrained()->cascadeOnDelete();
            $table->string('label'); // Red, Blue, XL, etc.
            $table->string('value'); // red, blue, xl
            $table->string('color_hex')->nullable(); // Per swatches: #FF0000
            $table->string('image_url')->nullable(); // Per swatches con immagine
            $table->integer('position')->default(0);
            $table->boolean('is_default')->default(false);
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index(['product_option_id', 'position']);
            $table->index('value');
        });

        // Pivot: Products <-> Options (many-to-many)
        // Questa tabella collega un prodotto alle sue opzioni
        Schema::create('product_product_option', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_option_id')->constrained()->cascadeOnDelete();
            $table->integer('position')->default(0);
            $table->boolean('is_required')->default(false);
            $table->timestamps();

            $table->unique(['product_id', 'product_option_id']);
            $table->index('position');
        });

        // Pivot: Variants <-> Option Values (many-to-many)
        // Questa tabella definisce quali valori di opzioni ha una specifica variante
        // Es: Variant #123 -> Color: Red, Size: XL
        Schema::create('product_variant_option_value', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_option_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_option_value_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_variant_id', 'product_option_id'], 'variant_option_unique');
            $table->index('product_option_value_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_option_value');
        Schema::dropIfExists('product_product_option');
        Schema::dropIfExists('product_option_values');
        Schema::dropIfExists('product_options');
    }
};
