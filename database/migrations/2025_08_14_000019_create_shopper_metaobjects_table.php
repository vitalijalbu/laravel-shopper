<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(shopper_table('metaobjects'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('definition_id')->constrained(shopper_table('metaobject_definitions'))->cascadeOnDelete();
            $table->string('handle')->unique();
            $table->json('fields');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['definition_id', 'handle']);
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(shopper_table('metaobjects'));
    }
};
