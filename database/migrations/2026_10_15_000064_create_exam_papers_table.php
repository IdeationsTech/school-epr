<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_papers', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('test_id');
            $table->date('scheduled_on');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('room', 160)->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't64_school_id');
            $table->unique(['school_id', 'test_id'], 't64_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_papers');
    }
};
