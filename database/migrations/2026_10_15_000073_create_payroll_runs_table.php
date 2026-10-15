<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('campus_id');
            $table->date('period');
            $table->unsignedInteger('revision');
            $table->string('status', 160);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't73_school_id');
            $table->unique(['school_id', 'campus_id', 'period', 'revision'], 't73_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_runs');
    }
};
