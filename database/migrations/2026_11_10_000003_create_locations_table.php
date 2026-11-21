<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('locations',function(Blueprint $t){$t->id();$t->string('building',100);$t->string('floor',50)->nullable();$t->string('room',100)->nullable();$t->text('notes')->nullable();$t->boolean('is_active')->default(true);$t->timestamps();}); } public function down(): void {Schema::dropIfExists('locations');} };
