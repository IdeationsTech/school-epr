<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('campus_id')->nullable();
            $table->string('title', 160);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('kind', 160);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't80_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
