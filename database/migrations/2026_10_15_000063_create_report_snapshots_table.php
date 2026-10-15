<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_snapshots', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('test_result_id');
            $table->unsignedBigInteger('grading_scheme_id');
            $table->unsignedInteger('revision');
            $table->json('payload');
            $table->unsignedBigInteger('published_by');
            $table->dateTime('published_at');
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't63_school_id');
            $table->unique(['school_id', 'test_result_id', 'revision'], 't63_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_snapshots');
    }
};
