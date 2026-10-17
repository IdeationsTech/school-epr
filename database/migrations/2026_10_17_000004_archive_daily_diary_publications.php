<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('diaries', function (Blueprint $t): void { $t->unsignedInteger('lock_version')->default(0); $t->json('snapshot')->nullable(); }); }
    public function down(): void { Schema::table('diaries', fn (Blueprint $t) => $t->dropColumn(['lock_version', 'snapshot'])); }
};
