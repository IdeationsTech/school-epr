<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_slots', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('staff_profile_id');
            $table->unsignedInteger('weekday');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('room', 160)->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't79_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_slots');
    }
};
