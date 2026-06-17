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
        // 1. Roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // 2. User Roles pivot table
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Seed default roles
        DB::table('roles')->insert([
            ['name' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'warga', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'm2m', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'developer', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. Audit Logs table for SOAP Audit
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('activity_name');
            $table->text('log_content');
            $table->string('receipt_number')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};
