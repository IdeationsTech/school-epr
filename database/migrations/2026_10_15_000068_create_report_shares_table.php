<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_shares', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('report_snapshot_id')->nullable();
            $table->unsignedBigInteger('consolidated_snapshot_id')->nullable();
            $table->unsignedBigInteger('guardian_id');
            $table->unsignedBigInteger('confirmed_by');
            $table->dateTime('confirmed_at');
            $table->string('channel', 160);
            $table->string('recipient_phone', 160);
            $table->text('resend_reason')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't68_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_shares');
    }
};
