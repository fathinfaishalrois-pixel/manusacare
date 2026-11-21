<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void {
 Schema::create('roles',function(Blueprint $t){$t->id();$t->string('name');$t->string('slug')->unique();$t->text('description')->nullable();$t->timestamps();});
 Schema::create('permissions',function(Blueprint $t){$t->id();$t->string('name');$t->string('slug')->unique();$t->text('description')->nullable();$t->timestamps();});
 Schema::create('user_roles',function(Blueprint $t){$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->foreignId('role_id')->constrained()->cascadeOnDelete();$t->primary(['user_id','role_id']);});
 Schema::create('role_permissions',function(Blueprint $t){$t->foreignId('role_id')->constrained()->cascadeOnDelete();$t->foreignId('permission_id')->constrained()->cascadeOnDelete();$t->primary(['role_id','permission_id']);});
 } public function down(): void {Schema::dropIfExists('role_permissions');Schema::dropIfExists('user_roles');Schema::dropIfExists('permissions');Schema::dropIfExists('roles');} };
