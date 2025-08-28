<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_types', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('database_strategy', ['shared', 'separate'])->default('shared');
            $table->json('database_config')->nullable();
            $table->json('auth_methods')->default('["email"]');
            $table->json('theme_config')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('has_landing_page')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index('database_strategy');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_types');
    }
};