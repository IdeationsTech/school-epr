<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_replies', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('support_ticket_id');
            $table->unsignedBigInteger('author_user_id');
            $table->text('body');
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't86_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_replies');
    }
};
