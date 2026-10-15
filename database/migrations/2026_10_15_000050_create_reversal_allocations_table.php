<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reversal_allocations', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('payment_reversal_id');
            $table->unsignedBigInteger('payment_allocation_id');
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't50_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reversal_allocations');
    }
};
