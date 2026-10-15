<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_candidates', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('exam_cycle_id');
            $table->unsignedBigInteger('enrollment_id');
            $table->string('roll_no', 160);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't66_school_id');
            $table->unique(['school_id', 'exam_cycle_id', 'enrollment_id'], 't66_u0');
            $table->unique(['school_id', 'exam_cycle_id', 'roll_no'], 't66_u1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_candidates');
    }
};
