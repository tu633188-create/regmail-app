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
        Schema::create('user_telegram_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('telegram_bot_token')->nullable();
            $table->string('telegram_chat_id')->nullable();
            $table->boolean('telegram_enabled')->default(false);
            $table->boolean('registration_notifications')->default(false);
            $table->boolean('error_notifications')->default(false);
            $table->boolean('quota_notifications')->default(false);
            $table->boolean('daily_summary')->default(false);
            $table->string('notification_language')->default('en');
            $table->json('custom_templates')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_telegram_settings');
    }
};
