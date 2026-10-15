<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_results', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('test_id');
            $table->unsignedBigInteger('enrollment_id');
            $table->decimal('marks', 12, 2)->nullable();
            $table->string('status', 160);
            $table->text('remarks')->nullable();
            $table->unsignedInteger('revision');
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't62_school_id');
            $table->unique(['school_id', 'test_id', 'enrollment_id'], 't62_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_results');
    }
};
