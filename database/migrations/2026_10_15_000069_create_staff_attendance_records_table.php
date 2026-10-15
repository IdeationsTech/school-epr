<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_attendance_records', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('staff_profile_id');
            $table->unsignedBigInteger('campus_id');
            $table->date('attendance_date');
            $table->string('status', 160);
            $table->time('arrived_at')->nullable();
            $table->unsignedBigInteger('leave_request_id')->nullable();
            $table->unsignedBigInteger('recorded_by');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't69_school_id');
            $table->unique(['school_id', 'staff_profile_id', 'attendance_date'], 't69_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendance_records');
    }
};
