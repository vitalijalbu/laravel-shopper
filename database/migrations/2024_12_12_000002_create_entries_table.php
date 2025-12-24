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
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->string('collection')->index();
            $table->string('slug')->comment('URL slug dell\'entry');
            $table->string('title')->comment('Titolo dell\'entry');
            $table->json('data')->nullable()->comment('Dati dell\'entry in formato JSON');
            $table->string('status')->default('draft')->comment('Stato: draft, published, scheduled');
            $table->timestamp('published_at')->nullable()->comment('Data di pubblicazione');
            $table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('locale')->default('it')->comment('Lingua dell\'entry');
            $table->foreignId('parent_id')->nullable()->constrained('entries')->onDelete('cascade')->comment('Entry parent per gerarchie');
            $table->integer('order')->default(0)->comment('Ordinamento manuale');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['collection', 'slug', 'locale']);
            $table->index(['collection', 'status']);
            $table->index(['collection', 'published_at']);
            $table->index('parent_id');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
