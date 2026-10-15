<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('admission_no', 160);
            $table->string('first_name', 160);
            $table->string('last_name', 160)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 160)->nullable();
            $table->date('admitted_on');
            $table->string('status', 160);
            $table->unsignedBigInteger('application_id')->nullable();
            $table->unsignedBigInteger('photo_file_id')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't35_school_id');
            $table->unique(['school_id', 'admission_no'], 't35_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
