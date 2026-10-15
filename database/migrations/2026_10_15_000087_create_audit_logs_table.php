<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('actor_membership_id')->nullable();
            $table->string('action', 160);
            $table->string('target_type', 160);
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('changes')->nullable();
            $table->dateTime('occurred_at');
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't87_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
