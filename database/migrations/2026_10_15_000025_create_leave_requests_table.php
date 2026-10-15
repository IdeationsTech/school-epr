<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('staff_profile_id');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('leave_type', 160);
            $table->text('reason')->nullable();
            $table->string('status', 160);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't25_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
