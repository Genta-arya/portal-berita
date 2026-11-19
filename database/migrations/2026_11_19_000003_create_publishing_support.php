<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->string('name');
            $t->string('path');
            $t->json('variants');
            $t->string('mime');
            $t->unsignedBigInteger('size');
            $t->unsignedInteger('width');
            $t->unsignedInteger('height');
            $t->string('alt')->nullable();
            $t->text('caption')->nullable();
            $t->string('credit')->nullable();
            $t->timestamps();
        });
        Schema::create('media_usages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('media_id')->constrained('media')->restrictOnDelete();
            $t->string('context_type');
            $t->unsignedBigInteger('context_id');
            $t->string('kind')->default('body');
            $t->string('alt')->nullable();
            $t->text('caption')->nullable();
            $t->string('credit')->nullable();
            $t->index(['context_type', 'context_id']);
        });
        Schema::table('articles', function (Blueprint $t) {
            $t->foreignId('hero_media_id')->nullable()->constrained('media')->restrictOnDelete();
            $t->string('hero_alt')->nullable();
            $t->string('hero_credit')->nullable();
            $t->text('hero_caption')->nullable();
        });
        Schema::create('featured_placements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('article_id')->constrained()->cascadeOnDelete();
            $t->string('location');
            $t->unsignedInteger('position');
            $t->timestamps();
            $t->unique(['location', 'position']);
        });
        Schema::create('breaking_news', function (Blueprint $t) {
            $t->id();
            $t->string('text');
            $t->string('url')->nullable();
            $t->timestamp('starts_at');
            $t->timestamp('ends_at');
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('ad_slots', function (Blueprint $t) {
            $t->string('name')->primary();
        });
        foreach (['home_top', 'list_middle', 'sidebar', 'article_bottom'] as $slot) {
            DB::table('ad_slots')->insert(['name' => $slot]);
        }
        Schema::create('ad_banners', function (Blueprint $t) {
            $t->id();
            $t->string('slot');
            $t->foreign('slot')->references('name')->on('ad_slots');
            $t->foreignId('media_id')->constrained('media')->restrictOnDelete();
            $t->string('alt');
            $t->string('url');
            $t->timestamp('starts_at');
            $t->timestamp('ends_at');
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('pages', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->longText('body');
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('site_settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->text('value')->nullable();
            $t->timestamps();
        });
        Schema::create('article_view_daily', function (Blueprint $t) {
            $t->id();
            $t->foreignId('article_id')->constrained()->cascadeOnDelete();
            $t->date('date');
            $t->unsignedBigInteger('views')->default(0);
            $t->unique(['article_id', 'date']);
        });
    }

    public function down(): void
    {
        foreach (['article_view_daily', 'site_settings', 'pages', 'ad_banners', 'ad_slots', 'breaking_news', 'featured_placements'] as $name) {
            Schema::dropIfExists($name);
        }
        Schema::table('articles', function (Blueprint $t) {
            $t->dropConstrainedForeignId('hero_media_id');
            $t->dropColumn(['hero_alt', 'hero_credit', 'hero_caption']);
        });
        Schema::dropIfExists('media_usages');
        Schema::dropIfExists('media');
    }
};
