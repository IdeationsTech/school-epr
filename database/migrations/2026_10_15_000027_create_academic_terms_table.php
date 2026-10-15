<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_terms', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('academic_session_id');
            $table->string('name', 160);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->unsignedInteger('position');
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't27_school_id');
            $table->unique(['school_id', 'academic_session_id', 'name'], 't27_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_terms');
    }
};
