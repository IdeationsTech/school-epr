<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_item_components', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('payroll_item_id');
            $table->string('name', 160);
            $table->string('kind', 160);
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't75_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_item_components');
    }
};
