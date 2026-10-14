<?php

namespace Library\Adapters\Outbound\Persistence\Models;

final class BookModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'library.book';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    use \Illuminate\Database\Eloquent\SoftDeletes;
    protected function casts(): array { return ['total_copies' => 'integer', 'available_copies' => 'integer', 'deleted_at' => 'immutable_datetime']; }
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(CategoryModel::class, 'category_id'); }
    public function loanItems(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(LoanItemModel::class, 'book_id'); }
}
