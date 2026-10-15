<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_payments', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('campus_id');
            $table->unsignedBigInteger('staff_profile_id');
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->dateTime('paid_at');
            $table->string('method', 160);
            $table->unsignedBigInteger('recorded_by');
            $table->string('reference', 160)->nullable();
            $table->string('idempotency_key', 160);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't76_school_id');
            $table->unique(['school_id', 'idempotency_key'], 't76_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
