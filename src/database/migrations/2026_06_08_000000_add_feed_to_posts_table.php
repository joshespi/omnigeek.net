<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // 'main' = the primary feed, 'memes' = low-signal junk feed.
            // String (not bool) leaves room for future feeds.
            $table->string('feed')->default('main')->index()->after('youtube_id');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('feed');
        });
    }
};
