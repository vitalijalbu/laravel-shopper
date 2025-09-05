<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('url')->nullable()->index();
            $table->boolean('is_default')->default(false)->index();
            $table->string('status')->default('active')->index();
            $table->jsonb('locales')->nullable();
            $table->jsonb('currencies')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'status']);
            $table->index(['slug', 'site_id']);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
