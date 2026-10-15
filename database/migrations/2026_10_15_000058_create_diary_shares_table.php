<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diary_shares', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('diary_id');
            $table->unsignedBigInteger('confirmed_by');
            $table->dateTime('confirmed_at');
            $table->string('channel', 160);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't58_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diary_shares');
    }
};
