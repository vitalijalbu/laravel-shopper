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
            $table->char('code', 2)->unique();
            $table->string('phone_code', 10)->nullable()->index();
            $table->boolean('is_enabled')->default(true)->index();
            $table->timestamps();

            $table->index(['code', 'is_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
