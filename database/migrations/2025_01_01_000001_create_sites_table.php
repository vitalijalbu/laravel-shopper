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
            $table->string('url')->index();
            $table->string('locale', 10)->index();
            $table->string('lang', 5)->index();
            $table->jsonb('attributes')->nullable();
            $table->integer('order')->default(0)->index();
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->index(['handle', 'status']);
            $table->index(['locale', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
