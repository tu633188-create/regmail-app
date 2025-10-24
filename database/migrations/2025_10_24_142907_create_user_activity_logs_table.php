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
        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // login, logout, api_call, registration_start, etc.
            $table->string('ip_address');
            $table->text('user_agent');
            $table->string('device_id')->nullable();
            $table->json('metadata')->nullable(); // Additional context data
            $table->timestamp('created_at');

            $table->index(['user_id', 'action']);
            $table->index(['created_at']);
            $table->index(['ip_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};
