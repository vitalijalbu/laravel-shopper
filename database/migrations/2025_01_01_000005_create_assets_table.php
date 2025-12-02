<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Asset Containers - Similar to Statamic's container system
     * Each container represents a disk/storage location with its own settings.
     */
    public function up(): void
    {
        Schema::create('asset_containers', function (Blueprint $table) {
            $table->id();
            $table->string('handle')->unique()->index();
            $table->string('title');
            $table->string('disk')->index(); // local, s3, cloudinary, etc.

            // Permissions
            $table->boolean('allow_uploads')->default(true);
            $table->boolean('allow_downloading')->default(true);
            $table->boolean('allow_renaming')->default(true);
            $table->boolean('allow_moving')->default(true);

            // Validation
            $table->jsonb('allowed_extensions')->nullable(); // ['jpg', 'png', 'pdf', 'mp4']
            $table->unsignedBigInteger('max_file_size')->nullable(); // in bytes

            // Settings (Statamic-style)
            $table->jsonb('settings')->nullable()->comment('Container-specific settings');

            // Glide presets for this container
            $table->jsonb('glide_presets')->nullable()->comment('Preset transformations');

            $table->timestamps();

            $table->index('disk');
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();

            // Container reference (not FK to allow flexibility)
            $table->string('container')->index();

            // Path components (Statamic-style)
            $table->string('folder')->index(); // products/shoes
            $table->string('basename')->index(); // image.jpg
            $table->string('filename')->index(); // image
            $table->char('extension', 10)->index(); // jpg
            $table->string('path')->index(); // products/shoes/image.jpg

            // File details
            $table->string('mime_type', 100)->index(); // image/jpeg, video/mp4, application/pdf
            $table->unsignedBigInteger('size')->index(); // bytes

            // Image/Video specific
            $table->unsignedInteger('width')->nullable()->index();
            $table->unsignedInteger('height')->nullable()->index();
            $table->unsignedInteger('duration')->nullable(); // for videos/audio in seconds
            $table->decimal('aspect_ratio', 8, 4)->nullable();

            // Metadata (Statamic-style JSONB)
            $table->jsonb('meta')->nullable()->comment('Alt text, title, caption, etc.');

            // Custom data fields
            $table->jsonb('data')->nullable()->comment('Custom fields via blueprints');

            // Focus point for smart cropping (Statamic feature)
            $table->string('focus_css')->nullable(); // "50-50" = center

            // Upload tracking
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            // File hash for deduplication
            $table->string('hash', 64)->nullable()->index(); // SHA-256

            $table->timestamps();
            $table->softDeletes();

            // Unique constraint
            $table->unique(['container', 'path']);

            // Composite indexes for common queries
            $table->index(['container', 'folder']);
            $table->index(['container', 'extension']);
            $table->index(['container', 'mime_type']);
            $table->index(['mime_type', 'extension']);
            $table->index(['uploaded_by', 'created_at']);
            $table->index(['hash', 'size']); // deduplication

            // Media type categorization
            $table->index(['mime_type', 'width', 'height']); // images
            $table->index(['mime_type', 'duration']); // videos/audio
        });

        // Asset transformations cache (Glide generated images)
        Schema::create('asset_transformations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();

            // Transformation params
            $table->string('preset')->nullable()->index(); // 'thumbnail', 'large', etc.
            $table->jsonb('params')->comment('Glide transformation parameters');
            $table->string('params_hash', 64)->index(); // Hash of params for quick lookup

            // Generated file
            $table->string('path')->index(); // cache/transformations/abc123.jpg
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            // Cache management
            $table->timestamp('last_accessed_at')->nullable()->index();
            $table->unsignedInteger('access_count')->default(0);

            $table->timestamps();

            $table->unique(['asset_id', 'params_hash']);
            $table->index(['preset', 'asset_id']);
            $table->index(['last_accessed_at']); // for cleanup
        });

        // Folders metadata (optional, for folder-level settings)
        Schema::create('asset_folders', function (Blueprint $table) {
            $table->id();
            $table->string('container')->index();
            $table->string('path')->index(); // products/shoes
            $table->string('basename'); // shoes
            $table->foreignId('parent_id')->nullable()->constrained('asset_folders')->nullOnDelete();

            // Folder metadata
            $table->string('title')->nullable();
            $table->jsonb('meta')->nullable();
            $table->jsonb('data')->nullable();

            // Permissions override
            $table->boolean('allow_uploads')->nullable();

            $table->timestamps();

            $table->unique(['container', 'path']);
            $table->index(['container', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_folders');
        Schema::dropIfExists('asset_transformations');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_containers');
    }
};
