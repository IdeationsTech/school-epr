<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_concessions', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('enrollment_id');
            $table->unsignedBigInteger('fee_head_id');
            $table->string('kind', 160);
            $table->decimal('value', 12, 2);
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't43_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_concessions');
    }
};
