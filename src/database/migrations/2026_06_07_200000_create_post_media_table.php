<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('type'); // 'image' | 'video'
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Migrate existing single-media posts into the new table.
        DB::table('posts')
            ->whereNotNull('media_path')
            ->get(['id', 'media_path', 'media_type'])
            ->each(function ($row) {
                DB::table('post_media')->insert([
                    'post_id'    => $row->id,
                    'path'       => $row->media_path,
                    'type'       => $row->media_type ?? 'image',
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_media');
    }
};
