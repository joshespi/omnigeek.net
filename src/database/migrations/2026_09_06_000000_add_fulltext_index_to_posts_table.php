<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // FULLTEXT indexes are a MySQL/MariaDB feature — sqlite (used in tests and
    // local dev by default) has no equivalent, so this is a no-op there and
    // Post::scopeSearch() falls back to LIKE on that driver.
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE posts ADD FULLTEXT posts_title_body_fulltext (title, body)');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE posts DROP INDEX posts_title_body_fulltext');
        }
    }
};
