<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_plans', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('academic_session_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('campus_id')->nullable();
            $table->string('name', 160);
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't41_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_plans');
    }
};
