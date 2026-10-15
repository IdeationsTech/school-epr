<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_roles', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('membership_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't21_school_id');
            $table->unique(['school_id', 'membership_id', 'role_id'], 't21_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_roles');
    }
};
