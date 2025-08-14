<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');

        Schema::create($prefix.'addresses', function (Blueprint $table) {
            $table->id();
            $table->morphs('addressable'); // Can be customer, order, etc.
            $table->foreignId('country_id')->nullable()->constrained($prefix.'countries');
            $table->string('type')->default('shipping'); // shipping, billing
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company')->nullable();
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code');
            $table->string('phone')->nullable();
            $table->boolean('default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');
        Schema::dropIfExists($prefix.'addresses');
    }
};
