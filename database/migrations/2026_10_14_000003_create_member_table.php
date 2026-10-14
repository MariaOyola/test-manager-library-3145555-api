<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE library.member (
    id uuid PRIMARY KEY, full_name varchar(200) NOT NULL, document_number varchar(30) NOT NULL UNIQUE
)
SQL
        );
    }

    public function down(): void
    {
        DB::statement('DROP TABLE library.member');
    }
};
