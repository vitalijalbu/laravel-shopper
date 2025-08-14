<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_default')->default(false);
            $table->string('share_token')->unique()->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'is_default']);
            $table->index('share_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
