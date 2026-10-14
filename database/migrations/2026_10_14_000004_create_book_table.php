<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE library.book (
    id uuid PRIMARY KEY, title varchar(250) NOT NULL, author varchar(200) NOT NULL,
    isbn char(13) NOT NULL, category_id uuid NOT NULL REFERENCES library.category(id) ON DELETE RESTRICT ON UPDATE NO ACTION,
    total_copies integer NOT NULL, available_copies integer NOT NULL,
    cover_key varchar(512), deleted_at timestamptz,
    CONSTRAINT ck_book_available_range CHECK (available_copies >= 0 AND available_copies <= total_copies)
)
SQL
        );
        DB::statement('CREATE UNIQUE INDEX ux_book_active_isbn ON library.book(isbn) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX ix_book_active_category_title ON library.book(category_id, title) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP TABLE library.book');
    }
};
