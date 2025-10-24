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
        Schema::create('api_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('endpoint');
            $table->string('method'); // GET, POST, PUT, DELETE
            $table->integer('response_code');
            $table->integer('response_time_ms');
            $table->string('ip_address');
            $table->text('user_agent');
            $table->string('device_id')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->timestamp('created_at');

            $table->index(['user_id', 'endpoint']);
            $table->index(['created_at']);
            $table->index(['response_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_usage_logs');
    }
};
