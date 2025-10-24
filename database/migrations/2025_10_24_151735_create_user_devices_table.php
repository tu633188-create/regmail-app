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
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('device_name')->nullable(); // Tên do user đặt
            $table->string('device_fingerprint')->unique(); // Fingerprint của device
            $table->string('device_type')->default('unknown'); // desktop, mobile, tablet
            $table->string('os')->nullable(); // Windows, macOS, Linux, iOS, Android
            $table->string('browser')->nullable(); // Chrome, Firefox, Safari, Edge
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
