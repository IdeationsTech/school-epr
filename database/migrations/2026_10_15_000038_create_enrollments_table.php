<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('section_id');
            $table->string('roll_no', 160)->nullable();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->string('status', 160);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't38_school_id');
            $table->unique(['school_id', 'student_id', 'section_id', 'starts_on'], 't38_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
