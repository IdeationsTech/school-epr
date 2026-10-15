<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diary_items', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('diary_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('author_membership_id');
            $table->text('classwork')->nullable();
            $table->text('homework')->nullable();
            $table->boolean('no_homework')->default(false);
            $table->string('status', 160);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't57_school_id');
            $table->unique(['school_id', 'diary_id', 'subject_id'], 't57_u0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diary_items');
    }
};
