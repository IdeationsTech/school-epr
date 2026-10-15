<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_memberships', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('user_id');
            $table->string('status', 160);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't20_school_id');
            $table->unique(['school_id', 'user_id'], 't20_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_memberships');
    }
};
