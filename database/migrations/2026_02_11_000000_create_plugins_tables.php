<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable(); // Optional icon
            $table->timestamps();
        });

        Schema::create('plugin_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained()->cascadeOnDelete();
            $table->string('version'); // e.g., 1.0.0
            $table->string('file_path');
            $table->text('changelog')->nullable();
            $table->timestamps();
        });

        Schema::create('plugin_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plugin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plugin_version_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Ensure a project has a plugin only once (version is managed)
            $table->unique(['project_id', 'plugin_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_project');
        Schema::dropIfExists('plugin_versions');
        Schema::dropIfExists('plugins');
    }
};
