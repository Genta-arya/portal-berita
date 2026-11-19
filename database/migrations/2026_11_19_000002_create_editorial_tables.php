<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('editor_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->index();
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('version')->default(1);
            $table->unsignedInteger('approved_version')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->text('withdrawal_reason')->nullable();
            $table->string('guest_author')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->boolean('sponsored')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'published_at', 'id']);
            $table->index(['status', 'scheduled_at']);
        });
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('article_tag', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->restrictOnDelete();
            $table->primary(['article_id', 'tag_id']);
        });
        Schema::create('article_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('version');
            $table->json('snapshot');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['article_id', 'version']);
        });
        Schema::create('editorial_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->text('body');
            $table->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action')->index();
            $table->string('target_type');
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['audit_logs', 'editorial_notes', 'article_revisions', 'article_tag', 'tags', 'articles', 'categories'] as $name) {
            Schema::dropIfExists($name);
        }
    }
};
