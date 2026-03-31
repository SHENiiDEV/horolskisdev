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
        Schema::table('plugins', function (Blueprint $table) {
            $table->boolean('enable_auto_update')->default(false)->after('image_path');
            $table->string('update_endpoint')->nullable()->after('enable_auto_update');
            $table->string('update_secret_key')->nullable()->after('update_endpoint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->dropColumn(['enable_auto_update', 'update_endpoint', 'update_secret_key']);
        });
    }
};
