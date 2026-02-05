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
        Schema::table('clients', function (Blueprint $table) {
            $table->decimal('default_hourly_rate', 8, 2)->nullable();
            $table->string('currency')->default('EUR');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->string('pricing_type')->default('hourly'); // hourly, fixed
            $table->decimal('rate', 8, 2)->nullable();
            $table->string('currency')->default('EUR');
            $table->decimal('estimated_hours', 8, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['default_hourly_rate', 'currency']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['pricing_type', 'rate', 'currency', 'estimated_hours']);
        });
    }
};
