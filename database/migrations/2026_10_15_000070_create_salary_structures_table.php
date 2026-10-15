<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_structures', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('staff_profile_id');
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->string('status', 160);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't70_school_id');
            $table->unique(['school_id', 'staff_profile_id', 'starts_on'], 't70_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_structures');
    }
};
