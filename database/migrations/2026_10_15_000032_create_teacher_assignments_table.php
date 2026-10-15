<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_assignments', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('staff_profile_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->boolean('is_class_teacher')->default(false);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't32_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_assignments');
    }
};
