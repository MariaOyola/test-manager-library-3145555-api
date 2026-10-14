<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE library.loan (
    id uuid PRIMARY KEY,
    member_id uuid NOT NULL REFERENCES library.member(id) ON DELETE RESTRICT ON UPDATE NO ACTION,
    loaned_by_user_id uuid NOT NULL REFERENCES library."user"(id) ON DELETE RESTRICT ON UPDATE NO ACTION,
    loaned_at timestamptz NOT NULL
)
SQL
        );
        DB::statement('CREATE INDEX ix_loan_loaned_at ON library.loan(loaned_at)');
    }

    public function down(): void
    {
        DB::statement('DROP TABLE library.loan');
    }
};
