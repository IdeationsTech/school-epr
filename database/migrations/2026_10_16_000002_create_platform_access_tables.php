<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('platform_admins',function(Blueprint $t): void {
            $t->id();$t->foreignId('user_id')->unique()->constrained('users')->restrictOnDelete();
            $t->boolean('active')->default(false);$t->timestamps();
        });
        Schema::create('platform_audit_logs',function(Blueprint $t): void {
            $t->id();$t->foreignId('actor_user_id')->constrained('users')->restrictOnDelete();
            $t->string('action',160);$t->string('target_type',160);$t->unsignedBigInteger('target_id');
            $t->json('changes')->nullable();$t->timestamps();
        });
    }
    public function down(): void {Schema::dropIfExists('platform_audit_logs');Schema::dropIfExists('platform_admins');}
};
