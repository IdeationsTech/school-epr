<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_advances', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('staff_profile_id');
            $table->decimal('amount', 12, 2);
            $table->date('paid_on')->nullable();
            $table->decimal('monthly_recovery', 12, 2);
            $table->string('status', 160);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't72_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_advances');
    }
};
