<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(shopper_table('metafield_definitions'), function (Blueprint $table) {
            $table->id();
            $table->string('namespace');
            $table->string('key');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type'); // text, number, boolean, json, date, url, file_reference, etc.
            $table->json('type_config')->nullable(); // Additional type configuration
            $table->json('validations')->nullable();
            $table->string('owner_type')->nullable(); // product, customer, order, etc.
            $table->boolean('is_required')->default(false);
            $table->boolean('is_unique')->default(false);
            $table->integer('list_position')->nullable();
            $table->timestamps();

            $table->unique(['namespace', 'key', 'owner_type']);
            $table->index(['owner_type', 'namespace']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(shopper_table('metafield_definitions'));
    }
};
