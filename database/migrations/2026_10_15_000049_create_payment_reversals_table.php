<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_reversals', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('payment_id');
            $table->decimal('amount', 12, 2);
            $table->text('reason');
            $table->string('status', 160);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('reversed_at')->nullable();
            $table->string('idempotency_key', 160);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't49_school_id');
            $table->unique(['school_id', 'idempotency_key'], 't49_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_reversals');
    }
};
