<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tests', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('exam_cycle_id')->nullable();
            $table->unsignedBigInteger('grading_scheme_id');
            $table->string('title', 160);
            $table->date('test_date');
            $table->decimal('max_marks', 12, 2);
            $table->string('assessment_type', 160);
            $table->string('status', 160);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't61_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
