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
            // Drop foreign key first (it depends on the unique index)
            $table->dropForeign(['user_id']);

            // Drop unique constraint on user_id (if exists)
            // This allows multiple bot configurations per user
            try {
                $table->dropUnique(['user_id']);
            } catch (\Exception $e) {
                // Unique constraint might not exist, continue
            }

            // Re-create foreign key
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_telegram_settings', function (Blueprint $table) {
            // Drop foreign key temporarily
            $table->dropForeign(['user_id']);

            // Restore unique constraint on user_id
            $table->unique('user_id');

            // Re-create foreign key
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
