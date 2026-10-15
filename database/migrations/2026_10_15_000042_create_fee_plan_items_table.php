<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_plan_items', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('fee_plan_id');
            $table->unsignedBigInteger('fee_head_id');
            $table->decimal('amount', 12, 2);
            $table->string('frequency', 160);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't42_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_plan_items');
    }
};
