<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');

        Schema::create($prefix.'product_options', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('select'); // select, color, size, etc.
            $table->json('values'); // Array of option values
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');
        Schema::dropIfExists($prefix.'product_options');
    }
};
