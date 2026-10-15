<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_closings', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('campus_id');
            $table->unsignedBigInteger('cashier_membership_id');
            $table->date('closing_date');
            $table->decimal('expected_cash', 12, 2);
            $table->decimal('counted_cash', 12, 2);
            $table->decimal('variance', 12, 2);
            $table->string('status', 160);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't51_school_id');
            $table->unique(['school_id', 'campus_id', 'cashier_membership_id', 'closing_date'], 't51_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashier_closings');
    }
};
