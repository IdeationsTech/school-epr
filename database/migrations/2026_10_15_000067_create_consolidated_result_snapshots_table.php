<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consolidated_result_snapshots', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('exam_cycle_id');
            $table->unsignedBigInteger('enrollment_id');
            $table->unsignedInteger('revision');
            $table->unsignedBigInteger('grading_scheme_id');
            $table->json('payload');
            $table->unsignedBigInteger('published_by');
            $table->dateTime('published_at');
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't67_school_id');
            $table->unique(['school_id', 'exam_cycle_id', 'enrollment_id', 'revision'], 't67_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consolidated_result_snapshots');
    }
};
