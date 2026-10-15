<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_profiles', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('membership_id');
            $table->string('employee_no', 160);
            $table->string('designation', 160)->nullable();
            $table->date('hired_on')->nullable();
            $table->string('status', 160);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't24_school_id');
            $table->unique(['school_id', 'membership_id'], 't24_u0');
            $table->unique(['school_id', 'employee_no'], 't24_u1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
    }
};
