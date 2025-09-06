<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->string('status')->default('active')->index();
            $table->json('seo')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Custom fields data (JSON schema-based)
            $table->jsonb('data')->nullable()->comment('Custom fields data based on JSON schema');
            
            // Additional filter indexes
            $table->index('name');
            $table->index('created_at');
            $table->index('updated_at');
            
            // Full text search (MySQL 5.6+)
            if (config('database.default') === 'mysql') {
                $table->fullText(['name', 'description']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
