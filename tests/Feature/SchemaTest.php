<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SchemaTest extends TestCase
{
    public function test_library_has_exactly_the_documented_columns_without_defaults(): void
    {
        $expected = [
            'category' => ['id', 'name'],
            'user' => ['id', 'username', 'password_hash', 'role'],
            'member' => ['id', 'full_name', 'document_number'],
            'book' => ['id', 'title', 'author', 'isbn', 'category_id', 'total_copies', 'available_copies', 'cover_key', 'deleted_at'],
            'loan' => ['id', 'member_id', 'loaned_by_user_id', 'loaned_at'],
            'loan_item' => ['id', 'loan_id', 'book_id', 'book_title', 'isbn', 'category_name', 'due_at', 'returned_at'],
        ];
        $columns = DB::select("SELECT table_name, column_name, data_type, is_nullable, column_default FROM information_schema.columns WHERE table_schema = 'library' ORDER BY table_name, ordinal_position");
        $actual = [];
        foreach ($columns as $column) {
            $actual[$column->table_name][] = $column->column_name;
            $this->assertNull($column->column_default);
            if (str_ends_with($column->column_name, '_at')) {
                $this->assertSame('timestamp with time zone', $column->data_type);
            }
            $this->assertSame(in_array($column->column_name, ['deleted_at', 'cover_key', 'returned_at'], true) ? 'YES' : 'NO', $column->is_nullable);
        }
        ksort($expected);
        $this->assertSame($expected, $actual);
        $this->assertSame(5, DB::table('library.category')->count());
        $this->assertSame('Tecnología', DB::table('library.category')->where('id', '33333333-3333-4333-8333-333333333333')->value('name'));
    }

    public function test_documented_constraints_and_partial_indexes_exist(): void
    {
        $constraints = DB::select("SELECT c.conname, c.contype, pg_get_constraintdef(c.oid) AS definition FROM pg_constraint c JOIN pg_namespace n ON n.oid = c.connamespace WHERE n.nspname = 'library'");
        $this->assertCount(6, array_filter($constraints, fn ($c) => $c->contype === 'p'));
        $foreign = array_values(array_filter($constraints, fn ($c) => $c->contype === 'f'));
        $this->assertCount(5, $foreign);
        $this->assertCount(4, array_filter($foreign, fn ($c) => str_contains($c->definition, 'ON DELETE RESTRICT')));
        $this->assertCount(1, array_filter($foreign, fn ($c) => str_contains($c->definition, 'ON DELETE CASCADE')));
        $this->assertContains('ck_book_available_range', array_column($constraints, 'conname'));
        // El documento deja estas dos reglas para T-18; no se adelantan silenciosamente.
        $this->assertNotContains('ck_book_total_positive', array_column($constraints, 'conname'));
        $this->assertNotContains('ck_user_role', array_column($constraints, 'conname'));
        $indexes = DB::select("SELECT indexname, indexdef FROM pg_indexes WHERE schemaname = 'library'");
        $definitions = array_column($indexes, 'indexdef', 'indexname');
        $this->assertStringContainsString('UNIQUE', $definitions['ux_book_active_isbn']);
        $this->assertStringContainsString('WHERE (deleted_at IS NULL)', $definitions['ux_book_active_isbn']);
        $this->assertStringContainsString('WHERE (returned_at IS NULL)', $definitions['ix_loan_item_open_due_at']);
    }

    public function test_admin_provisioning_is_idempotent_and_does_not_replace_password(): void
    {
        $before = DB::table('library.user')->where('username', 'admin')->first();
        $this->artisan('library:provision-admin')->assertSuccessful();
        $after = DB::table('library.user')->where('username', 'admin')->first();
        $this->assertSame($before->id, $after->id);
        $this->assertSame($before->password_hash, $after->password_hash);
        $this->assertSame('admin', $after->role);
        $this->assertNotSame('isolated-admin-password', $after->password_hash);
        $this->assertSame(12, password_get_info($after->password_hash)['options']['cost']);
    }
}
