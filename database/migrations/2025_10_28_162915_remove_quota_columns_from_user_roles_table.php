<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('user_roles')) {
            Schema::table('user_roles', function (Blueprint $table) {
                // Check and drop columns if they exist
                $columnsToDrop = [];

                if (Schema::hasColumn('user_roles', 'device_limit')) {
                    $columnsToDrop[] = 'device_limit';
                }

                if (Schema::hasColumn('user_roles', 'monthly_quota')) {
                    $columnsToDrop[] = 'monthly_quota';
                }

                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_roles', function (Blueprint $table) {
            $table->integer('device_limit')->default(1);
            $table->integer('monthly_quota')->default(10);
        });
    }
};
