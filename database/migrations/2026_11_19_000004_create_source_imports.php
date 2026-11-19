<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('source_imports', function (Blueprint $t) {
            $t->id();
            $t->string('source');
            $t->string('type');
            $t->string('source_id');
            $t->unsignedBigInteger('local_id');
            $t->json('metadata')->nullable();
            $t->timestamps();
            $t->unique(['source', 'type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('source_imports');
    }
};
