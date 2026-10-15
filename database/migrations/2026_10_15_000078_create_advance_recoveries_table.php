<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advance_recoveries', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('staff_advance_id');
            $table->unsignedBigInteger('payroll_item_id');
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't78_school_id');
            $table->unique(['school_id', 'staff_advance_id', 'payroll_item_id'], 't78_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_recoveries');
    }
};
