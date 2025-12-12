<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('email');
            $table->string('provider_id')->nullable()->after('provider');
            $table->text('provider_token')->nullable()->after('provider_id');
            $table->text('provider_refresh_token')->nullable()->after('provider_token');
            $table->string('avatar')->nullable()->after('provider_refresh_token');
            $table->timestamp('email_verified_at')->nullable()->change();
            $table->string('password')->nullable()->change();

            $table->index(['provider', 'provider_id']);
        });

        // Create social_accounts table for multiple provider support per user
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider'); // google, facebook, twitter, etc.
            $table->string('provider_id');
            $table->text('provider_token')->nullable();
            $table->text('provider_refresh_token')->nullable();
            $table->json('provider_data')->nullable(); // Store additional provider data
            $table->timestamps();

            $table->unique(['provider', 'provider_id']);
            $table->index(['user_id', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['provider', 'provider_id']);
            $table->dropColumn([
                'provider',
                'provider_id',
                'provider_token',
                'provider_refresh_token',
                'avatar',
            ]);
            $table->timestamp('email_verified_at')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });

        Schema::dropIfExists('social_accounts');
    }
};
