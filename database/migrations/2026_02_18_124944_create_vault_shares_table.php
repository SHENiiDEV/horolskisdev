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
        Schema::create('vault_shares', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->text('value'); // Encrypted
            $table->string('name')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('allowed_views')->default(1);
            $table->integer('views')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vault_shares');
    }
};
