<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_payment_allocations', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('salary_payment_id');
            $table->unsignedBigInteger('payroll_item_id');
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't77_school_id');
            $table->unique(['school_id', 'salary_payment_id', 'payroll_item_id'], 't77_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payment_allocations');
    }
};
