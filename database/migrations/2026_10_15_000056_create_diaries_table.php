<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diaries', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('section_id');
            $table->date('diary_date');
            $table->unsignedInteger('revision');
            $table->string('status', 160);
            $table->unsignedBigInteger('published_by')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->unsignedBigInteger('supersedes_id')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't56_school_id');
            $table->unique(['school_id', 'section_id', 'diary_date', 'revision'], 't56_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diaries');
    }
};
