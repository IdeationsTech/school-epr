<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('campus_id')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->string('disk', 160);
            $table->string('path', 160);
            $table->string('original_name', 160);
            $table->string('mime_type', 160);
            $table->unsignedInteger('size_bytes');
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't23_school_id');
            $table->unique(['school_id', 'disk', 'path'], 't23_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
