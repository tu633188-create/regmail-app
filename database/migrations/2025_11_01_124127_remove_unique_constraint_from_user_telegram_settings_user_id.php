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
            // Drop unique constraint on user_id (if exists)
            // This allows multiple bot configurations per user
            try {
                $table->dropUnique(['user_id']);
            } catch (\Exception $e) {
                // Unique constraint might not exist, continue
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_telegram_settings', function (Blueprint $table) {
            // Restore unique constraint on user_id
            $table->unique('user_id');
        });
    }
};
