<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_gate_passes', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('enrollment_id');
            $table->unsignedBigInteger('pickup_guardian_id')->nullable();
            $table->unsignedBigInteger('approved_by');
            $table->text('reason');
            $table->dateTime('departed_at')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't89_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_gate_passes');
    }
};
