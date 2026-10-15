<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_inquiries', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('campus_id');
            $table->string('child_name', 160);
            $table->string('guardian_name', 160);
            $table->string('phone', 160);
            $table->string('status', 160);
            $table->date('follow_up_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't33_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_inquiries');
    }
};
