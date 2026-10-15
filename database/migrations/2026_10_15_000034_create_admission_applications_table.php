<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_applications', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('inquiry_id')->nullable();
            $table->unsignedBigInteger('campus_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('academic_session_id');
            $table->string('application_no', 160);
            $table->string('student_name', 160);
            $table->date('birth_date')->nullable();
            $table->json('guardian_details');
            $table->string('status', 160);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't34_school_id');
            $table->unique(['school_id', 'application_no'], 't34_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_applications');
    }
};
