<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('section_id');
            $table->date('attendance_date');
            $table->string('status', 160);
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't54_school_id');
            $table->unique(['school_id', 'section_id', 'attendance_date'], 't54_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
