<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_invoices', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('subscription_id');
            $table->string('number', 160);
            $table->date('issued_on');
            $table->date('due_on');
            $table->decimal('amount', 12, 2);
            $table->string('status', 160);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't17_school_id');
            $table->unique(['school_id', 'number'], 't17_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
    }
};
