<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('name', 160);
            $table->string('scope', 160);
            $table->timestamps();
            $table->unique(['name'], 't13_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
