<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaint_updates', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('parent_complaint_id');
            $table->unsignedBigInteger('author_membership_id');
            $table->text('body');
            $table->string('status', 160);
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't91_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_updates');
    }
};
