<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('attendance_session_id');
            $table->unsignedBigInteger('enrollment_id');
            $table->string('status', 160);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't55_school_id');
            $table->unique(['school_id', 'attendance_session_id', 'enrollment_id'], 't55_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
