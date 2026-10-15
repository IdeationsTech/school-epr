<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('enrollment_id');
            $table->string('number', 160);
            $table->date('period');
            $table->string('kind', 160);
            $table->unsignedInteger('installment_no');
            $table->date('issued_on');
            $table->date('due_on');
            $table->string('status', 160);
            $table->decimal('total', 12, 2);
            $table->text('void_reason')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't44_school_id');
            $table->unique(['school_id', 'number'], 't44_u0');
            $table->unique(['school_id', 'enrollment_id', 'period', 'kind', 'installment_no'], 't44_u1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
