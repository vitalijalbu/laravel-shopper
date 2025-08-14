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
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->string('name')->index();
            $table->string('type');
            $table->text('description')->nullable();
            $table->jsonb('access')->nullable();
            $table->jsonb('capabilities')->nullable();
            $table->jsonb('displayable_fields')->nullable();
            $table->boolean('is_single')->default(false)->index();
            $table->timestamps();

            $table->unique(['type', 'site_id']);
            $table->index(['type', 'is_single']);
            $table->index(['site_id', 'is_single']);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(shopper_table('metaobject_definitions'));
    }
};
