<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_guardians', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('guardian_id');
            $table->string('relationship', 160);
            $table->boolean('is_primary')->default(false);
            $table->boolean('can_receive_reports')->default(false);
            $table->boolean('can_pick_up')->default(false);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't37_school_id');
            $table->unique(['school_id', 'student_id', 'guardian_id'], 't37_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_guardians');
    }
};
