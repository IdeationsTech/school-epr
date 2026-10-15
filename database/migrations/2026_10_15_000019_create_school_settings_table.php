<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_settings', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('key', 160);
            $table->json('value')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't19_school_id');
            $table->unique(['school_id', 'key'], 't19_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
