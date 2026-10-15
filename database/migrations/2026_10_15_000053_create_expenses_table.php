<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('campus_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('incurred_on');
            $table->text('description');
            $table->string('method', 160);
            $table->string('status', 160);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('file_id')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'id'], 't53_school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
