<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_observations', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('enrollment_id');
            $table->unsignedBigInteger('author_membership_id');
            $table->string('kind', 160);
            $table->text('note');
            $table->date('observed_on');
            $table->date('follow_up_on')->nullable();
            $table->string('status', 160);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't92_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_observations');
    }
};
