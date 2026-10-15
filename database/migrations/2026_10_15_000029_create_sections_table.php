<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('campus_id');
            $table->unsignedBigInteger('academic_session_id');
            $table->unsignedBigInteger('class_id');
            $table->string('name', 160);
            $table->unsignedInteger('capacity')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't29_school_id');
            $table->unique(['school_id', 'campus_id', 'academic_session_id', 'class_id', 'name'], 't29_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
