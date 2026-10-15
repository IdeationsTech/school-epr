<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section_subjects', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('subject_id');
            $table->boolean('required_for_diary')->default(false);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't31_school_id');
            $table->unique(['school_id', 'section_id', 'subject_id'], 't31_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_subjects');
    }
};
