<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('name', 160);
            $table->string('code', 160);
            $table->string('timezone', 160);
            $table->string('currency', 160);
            $table->string('status', 160);
            $table->timestamps();
            $table->unique(['code'], 't10_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
