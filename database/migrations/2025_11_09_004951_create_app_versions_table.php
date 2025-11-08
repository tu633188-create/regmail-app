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
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version', 50); // e.g., "1.0.0", "1.2.3"
            $table->integer('version_code')->unique(); // e.g., 100, 123 (for easy comparison)
            $table->string('file_path'); // Path to exe file
            $table->string('file_name'); // Original filename
            $table->bigInteger('file_size'); // File size in bytes
            $table->text('release_notes')->nullable(); // Changelog
            $table->boolean('is_active')->default(false); // Only one active version
            $table->boolean('is_force_update')->default(false); // Force users to update
            $table->string('checksum', 64)->nullable(); // SHA256 checksum for verification
            $table->timestamps();
            
            $table->index('version_code');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
