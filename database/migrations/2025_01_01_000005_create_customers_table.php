<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->string('first_name')->index();
            $table->string('last_name')->index();
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable()->index();
            $table->string('password');
            $table->string('phone', 20)->nullable()->index();
            $table->date('date_of_birth')->nullable()->index();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->boolean('is_enabled')->default(true)->index();
            $table->timestamp('last_login_at')->nullable()->index();
            $table->string('last_login_ip', 45)->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['email', 'site_id']);
            $table->index(['site_id', 'is_enabled']);
            $table->index(['last_name', 'first_name']);
            $table->index(['phone', 'site_id']);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
