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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'device_limit',
                'monthly_quota', 
                'used_quota',
                'quota_reset_at'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('device_limit')->default(1);
            $table->integer('monthly_quota')->default(10);
            $table->integer('used_quota')->default(0);
            $table->timestamp('quota_reset_at')->nullable();
        });
    }
};