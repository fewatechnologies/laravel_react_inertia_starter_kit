<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = 'shop_mysql';
        
        Schema::connection($connection)->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password');
            
            $table->boolean('is_active')->default(true);
            $table->json('profile_data')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            // Standard unique constraints
            $table->unique('email');
            $table->unique('phone');
            
            // Indexes for performance
            $table->index(['email', 'is_active']);
            $table->index(['phone', 'is_active']);
        });
    }

    public function down(): void
    {
        $connection = 'shop_mysql';
        Schema::connection($connection)->dropIfExists('users');
    }
};