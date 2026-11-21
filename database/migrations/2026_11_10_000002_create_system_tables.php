<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void {
 Schema::create('notifications',function(Blueprint $t){$t->uuid('id')->primary();$t->string('type');$t->morphs('notifiable');$t->text('data');$t->timestamp('read_at')->nullable();$t->timestamps();});
 Schema::create('activity_logs',function(Blueprint $t){$t->id();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->string('action');$t->string('subject_type')->nullable();$t->string('subject_id')->nullable();$t->json('metadata')->nullable();$t->string('ip_address',45)->nullable();$t->timestamps();$t->index(['subject_type','subject_id']);});
 Schema::create('reviews',function(Blueprint $t){$t->id();$t->uuid('report_id')->unique();$t->foreign('report_id')->references('id')->on('reports')->cascadeOnDelete();$t->foreignId('user_id')->constrained()->restrictOnDelete();$t->unsignedTinyInteger('rating');$t->text('comment')->nullable();$t->timestamps();});
 Schema::create('settings',function(Blueprint $t){$t->id();$t->string('key')->unique();$t->text('value')->nullable();$t->timestamps();});
 } public function down(): void {Schema::dropIfExists('settings');Schema::dropIfExists('reviews');Schema::dropIfExists('activity_logs');Schema::dropIfExists('notifications');} };
