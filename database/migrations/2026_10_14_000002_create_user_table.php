<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE library."user" (
    id uuid PRIMARY KEY, username varchar(120) NOT NULL UNIQUE,
    password_hash varchar(512) NOT NULL, role varchar(40) NOT NULL
)
SQL
        );
    }

    public function down(): void
    {
        DB::statement('DROP TABLE library."user"');
    }
};
