<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_logs', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('campus_id');
            $table->string('visitor_name', 160);
            $table->string('purpose', 160);
            $table->dateTime('entered_at');
            $table->dateTime('exited_at')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't88_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};
