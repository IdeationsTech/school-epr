<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_documents', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('file_id');
            $table->string('document_type', 160);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't39_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_documents');
    }
};
