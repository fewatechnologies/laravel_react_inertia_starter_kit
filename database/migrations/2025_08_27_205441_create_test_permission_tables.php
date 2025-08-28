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
        Schema::connection('test_mysql')->create('test_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('test');
            $table->string('description')->nullable();
            $table->timestamps();
            
            $table->unique(['name', 'guard_name']);
        });

        Schema::connection('test_mysql')->create('test_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('test');
            $table->string('description')->nullable();
            $table->timestamps();
            
            $table->unique(['name', 'guard_name']);
        });

        Schema::connection('test_mysql')->create('test_model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'test_model_has_permissions_model_id_model_type_index');
            
            $table->foreign('permission_id')
                ->references('id')
                ->on('test_permissions')
                ->onDelete('cascade');
                
            $table->primary(['permission_id', 'model_id', 'model_type'], 'test_model_has_permissions_permission_model_type_primary');
        });

        Schema::connection('test_mysql')->create('test_model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'test_model_has_roles_model_id_model_type_index');
            
            $table->foreign('role_id')
                ->references('id')
                ->on('test_roles')
                ->onDelete('cascade');
                
            $table->primary(['role_id', 'model_id', 'model_type'], 'test_model_has_roles_role_model_type_primary');
        });

        Schema::connection('test_mysql')->create('test_role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            
            $table->foreign('permission_id')
                ->references('id')
                ->on('test_permissions')
                ->onDelete('cascade');
                
            $table->foreign('role_id')
                ->references('id')
                ->on('test_roles')
                ->onDelete('cascade');
                
            $table->primary(['permission_id', 'role_id'], 'test_role_has_permissions_permission_id_role_id_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('test_mysql')->dropIfExists('test_role_has_permissions');
        Schema::connection('test_mysql')->dropIfExists('test_model_has_roles');
        Schema::connection('test_mysql')->dropIfExists('test_model_has_permissions');
        Schema::connection('test_mysql')->dropIfExists('test_permissions');
        Schema::connection('test_mysql')->dropIfExists('test_roles');
    }
};
