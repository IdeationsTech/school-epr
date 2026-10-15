<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_weightages', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('exam_cycle_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('subject_id');
            $table->string('assessment_type', 160);
            $table->decimal('weight', 12, 2);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't65_school_id');
            $table->unique(['school_id', 'exam_cycle_id', 'class_id', 'subject_id', 'assessment_type'], 't65_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_weightages');
    }
};
