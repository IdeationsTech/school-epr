<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grading_schemes', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name', 160);
            $table->unsignedInteger('version');
            $table->json('rules');
            $table->string('rounding_policy', 160);
            $table->string('tie_policy', 160);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't59_school_id');
            $table->unique(['school_id', 'name', 'version'], 't59_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grading_schemes');
    }
};
