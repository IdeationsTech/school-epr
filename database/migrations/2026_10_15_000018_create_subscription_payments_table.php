<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('subscription_invoice_id');
            $table->decimal('amount', 12, 2);
            $table->dateTime('paid_at');
            $table->string('method', 160);
            $table->string('reference', 160)->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't18_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
