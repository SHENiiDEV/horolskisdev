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
        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id')->index();
            $table->string('sender_name')->nullable(); // username or first_name
            $table->text('message_text')->nullable();
            $table->json('payload')->nullable(); // Full JSON dump
            $table->timestamp('processed_at')->nullable(); // If we act on it later
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_messages');
    }
};
