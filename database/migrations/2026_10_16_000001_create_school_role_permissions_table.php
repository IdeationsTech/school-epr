<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('school_role_permissions', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->restrictOnDelete();
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->restrictOnDelete();
            $table->boolean('enabled')->default(false);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't93_school_id');
            $table->unique(['school_id', 'role_id', 'permission_id'], 't93_u0');
        });
    }
    public function down(): void { Schema::dropIfExists('school_role_permissions'); }
};
