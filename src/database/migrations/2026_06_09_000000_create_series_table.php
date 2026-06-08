<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::table('posts', function (Blueprint $table) {
            // Shared series — any author can add a post; ordering is an explicit part number.
            $table->foreignId('series_id')->nullable()->after('feed')->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('series_part')->nullable()->after('series_id');
            $table->index(['series_id', 'series_part']);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('series_id');
            $table->dropColumn('series_part');
        });

        Schema::dropIfExists('series');
    }
};
