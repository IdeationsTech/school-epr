<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ptm_appointments', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('enrollment_id');
            $table->unsignedBigInteger('guardian_id');
            $table->unsignedBigInteger('staff_profile_id');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status', 160);
            $table->text('notes')->nullable();
            $table->date('follow_up_on')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't81_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ptm_appointments');
    }
};
