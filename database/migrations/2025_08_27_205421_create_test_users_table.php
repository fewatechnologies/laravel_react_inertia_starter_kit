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
        Schema::connection('test_mysql')->create('test_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->json('theme_preferences')->nullable();
            $table->string('language', 10)->default('en');
            $table->string('timezone', 50)->default('UTC');
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['email', 'is_active']);
            $table->index(['phone', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('test_mysql')->dropIfExists('test_users');
    }
};
