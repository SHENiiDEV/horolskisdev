<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('is_monitoring_enabled')->default(true)->after('status');
            $table->string('last_check_status')->nullable()->after('is_monitoring_enabled'); // 'up', 'down'
            $table->timestamp('last_check_time')->nullable()->after('last_check_status');
            $table->timestamp('down_since')->nullable()->after('last_check_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'is_monitoring_enabled',
                'last_check_status',
                'last_check_time',
                'down_since',
            ]);
        });
    }
};
