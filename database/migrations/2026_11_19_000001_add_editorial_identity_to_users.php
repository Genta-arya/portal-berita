<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('penulis')->index();
            $table->boolean('active')->default(true)->index();
            $table->string('slug')->nullable()->unique();
            $table->text('bio')->nullable();
            $table->unsignedInteger('session_version')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropIndex(['role']);
            $table->dropIndex(['active']);
            $table->dropColumn(['role', 'active', 'slug', 'bio', 'session_version']);
        });
    }
};
