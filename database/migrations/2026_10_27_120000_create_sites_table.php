<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('key', 32)->unique();
            $table->json('allowed_origins');
            $table->string('start_url', 2048);
            $table->string('repository')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('reviewers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('site_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('login_code_hash', 64)->nullable()->unique();
            $table->timestamp('login_code_expires_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'email']);
        });

        Schema::create('reviewer_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('reviewer_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->string('user_agent')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('feedback', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('site_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('reviewer_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->string('url', 2048);
            $table->string('path', 2048);
            $table->string('status')->default('open');
            $table->json('context')->nullable();
            $table->json('element')->nullable();
            $table->json('console')->nullable();
            $table->json('network')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_note')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'status', 'created_at']);
        });

        Schema::create('screenshots', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('feedback_id')->constrained('feedback')->cascadeOnDelete();
            $table->string('path');
            $table->string('mime');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->unsignedInteger('size');
            $table->string('kind');
            $table->unsignedTinyInteger('position');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screenshots');
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('reviewer_tokens');
        Schema::dropIfExists('reviewers');
        Schema::dropIfExists('sites');
    }
};
