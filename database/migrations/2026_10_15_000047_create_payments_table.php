<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('campus_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->unsignedBigInteger('received_by');
            $table->string('receipt_no', 160);
            $table->decimal('amount', 12, 2);
            $table->string('method', 160);
            $table->string('reference', 160)->nullable();
            $table->dateTime('paid_at');
            $table->string('idempotency_key', 160);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't47_school_id');
            $table->unique(['school_id', 'receipt_no'], 't47_u0');
            $table->unique(['school_id', 'idempotency_key'], 't47_u1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
