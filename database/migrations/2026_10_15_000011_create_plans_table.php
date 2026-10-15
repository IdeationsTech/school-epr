<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('code', 160);
            $table->string('name', 160);
            $table->unsignedInteger('student_limit');
            $table->unsignedInteger('campus_limit');
            $table->decimal('monthly_price', 12, 2);
            $table->json('features')->nullable();
            $table->timestamps();
            $table->unique(['code'], 't11_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
