<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardians', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name', 160);
            $table->string('phone', 160);
            $table->string('alternate_phone', 160)->nullable();
            $table->string('email', 160)->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't36_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
