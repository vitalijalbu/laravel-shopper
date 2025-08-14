<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->morphs('favoriteable'); // products, brands, categories, etc.
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('product'); // product, brand, category, collection
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['favoriteable_type', 'favoriteable_id', 'customer_id']);
            $table->index(['customer_id', 'type']);
            $table->index(['favoriteable_type', 'favoriteable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
