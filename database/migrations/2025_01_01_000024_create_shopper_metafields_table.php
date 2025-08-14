<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(shopper_table('metafields'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('definition_id')->nullable()->constrained(shopper_table('metafield_definitions'))->nullOnDelete();
            $table->string('namespace');
            $table->string('key');
            $table->morphs('metafieldable'); // owner_type, owner_id
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->unique(['namespace', 'key', 'metafieldable_type', 'metafieldable_id'], 'metafields_unique');
            $table->index(['metafieldable_type', 'metafieldable_id']);
            $table->index(['namespace', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(shopper_table('metafields'));
    }
};
