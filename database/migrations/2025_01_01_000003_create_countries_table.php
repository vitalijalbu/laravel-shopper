<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->char('code', 2)->unique()->index(); // ISO 3166-1 alpha-2
            $table->char('code_alpha3', 3)->nullable()->unique(); // ISO 3166-1 alpha-3
            $table->string('phone_code', 10)->nullable()->index();
            $table->string('currency', 3)->nullable()->index(); // ISO 4217
            $table->string('continent', 2)->nullable()->index(); // AF, AS, EU, NA, OC, SA, AN
            $table->jsonb('timezones')->nullable(); // Available timezones
            $table->boolean('requires_state')->default(false); // Does this country require state/province?
            $table->boolean('requires_postal_code')->default(true);
            $table->string('postal_code_format')->nullable(); // Regex for validation
            $table->string('status')->default('active')->index();
            $table->jsonb('metadata')->nullable(); // Additional country data
            $table->timestamps();

            $table->index(['code', 'status']);
            $table->index(['continent', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
