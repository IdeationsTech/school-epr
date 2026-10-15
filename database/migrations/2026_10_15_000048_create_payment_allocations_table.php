<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_allocations', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't48_school_id');
            $table->unique(['school_id', 'payment_id', 'invoice_id'], 't48_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};
