<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_issues', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('template_id');
            $table->string('number', 160);
            $table->json('payload');
            $table->unsignedBigInteger('issued_by');
            $table->dateTime('issued_at');
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't83_school_id');
            $table->unique(['school_id', 'number'], 't83_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_issues');
    }
};
