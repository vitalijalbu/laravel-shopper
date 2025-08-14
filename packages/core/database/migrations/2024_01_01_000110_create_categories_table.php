<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');

        Schema::create($prefix.'categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('position')->default(0)->index();
            $table->boolean('is_enabled')->default(true)->index();
            $table->timestamps();
            
            $table->foreign('parent_id')->references('id')->on($prefix.'categories')->cascadeOnDelete();
            $table->index(['parent_id', 'position']);
        });
    }

    public function down(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');
        Schema::dropIfExists($prefix.'categories');
    }
};
