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
        Schema::table('user_telegram_settings', function (Blueprint $table) {
            // Add unique constraint on (user_id, telegram_bot_token) to prevent duplicate bots per user
            $table->unique(['user_id', 'telegram_bot_token'], 'user_bot_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_telegram_settings', function (Blueprint $table) {
            $table->dropUnique('user_bot_unique');
        });
    }
};
