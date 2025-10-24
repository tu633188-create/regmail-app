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
        Schema::table('user_devices', function (Blueprint $table) {
            $table->string('device_type')->default('unknown')->after('device_name');
            $table->string('os')->nullable()->after('device_type');
            $table->string('browser')->nullable()->after('os');
            $table->timestamp('last_used_at')->nullable()->after('last_seen_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            $table->dropColumn(['device_type', 'os', 'browser', 'last_used_at']);
        });
    }
};
