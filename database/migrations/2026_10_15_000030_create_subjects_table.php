<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name', 160);
            $table->string('code', 160);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't30_school_id');
            $table->unique(['school_id', 'code'], 't30_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
