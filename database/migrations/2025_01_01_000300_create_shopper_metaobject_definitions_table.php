<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(shopper_table('metaobject_definitions'), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->unique();
            $table->text('description')->nullable();
            $table->json('access')->nullable();
            $table->json('capabilities')->nullable();
            $table->json('displayable_fields')->nullable();
            $table->boolean('is_single')->default(false);
            $table->timestamps();

            $table->index(['type', 'is_single']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(shopper_table('metaobject_definitions'));
    }
};
