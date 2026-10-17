<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{Schema::table('invoice_items',fn(Blueprint $t)=>$t->string('billing_frequency',160)->nullable());}
 public function down():void{Schema::table('invoice_items',fn(Blueprint $t)=>$t->dropColumn('billing_frequency'));}
};
