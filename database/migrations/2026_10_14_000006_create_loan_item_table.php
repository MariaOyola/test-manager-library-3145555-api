<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE library.loan_item (
    id uuid PRIMARY KEY,
    loan_id uuid NOT NULL REFERENCES library.loan(id) ON DELETE CASCADE ON UPDATE NO ACTION,
    book_id uuid NOT NULL REFERENCES library.book(id) ON DELETE RESTRICT ON UPDATE NO ACTION,
    book_title varchar(250) NOT NULL, isbn char(13) NOT NULL, category_name varchar(120) NOT NULL,
    due_at timestamptz NOT NULL, returned_at timestamptz,
    UNIQUE (loan_id, book_id)
)
SQL
        );
        DB::statement('CREATE INDEX ix_loan_item_book_id ON library.loan_item(book_id)');
        DB::statement('CREATE INDEX ix_loan_item_open_due_at ON library.loan_item(due_at) WHERE returned_at IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP TABLE library.loan_item');
    }
};
