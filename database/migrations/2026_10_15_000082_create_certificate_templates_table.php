<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_templates', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name', 160);
            $table->string('type', 160);
            $table->text('body');
            $table->unsignedInteger('version');
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't82_school_id');
            $table->unique(['school_id', 'name', 'version'], 't82_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
    }
};
