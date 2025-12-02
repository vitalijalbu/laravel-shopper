<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->jsonb('countries'); // Array of country ISO codes
            $table->jsonb('states')->nullable(); // Array of state codes for specific countries
            $table->jsonb('postcodes')->nullable(); // Array of postcode patterns
            $table->string('status')->default('active')->index();
            $table->jsonb('data')->nullable()->comment('Custom fields data');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['site_id', 'status']);
            $table->index(['status', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_zones');
    }
};
