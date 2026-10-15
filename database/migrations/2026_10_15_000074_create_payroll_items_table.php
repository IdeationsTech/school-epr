<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_items', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('payroll_run_id');
            $table->unsignedBigInteger('staff_profile_id');
            $table->unsignedBigInteger('salary_structure_id');
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('deduction_amount', 12, 2);
            $table->decimal('advance_recovery', 12, 2);
            $table->decimal('net_amount', 12, 2);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't74_school_id');
            $table->unique(['school_id', 'payroll_run_id', 'staff_profile_id'], 't74_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};
