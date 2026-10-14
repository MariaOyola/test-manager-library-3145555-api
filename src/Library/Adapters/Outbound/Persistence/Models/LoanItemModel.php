<?php

namespace Library\Adapters\Outbound\Persistence\Models;

final class LoanItemModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'library.loan_item';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected function casts(): array { return ['due_at' => 'immutable_datetime', 'returned_at' => 'immutable_datetime']; }
    public function loan(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(LoanModel::class, 'loan_id'); }
    public function book(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(BookModel::class, 'book_id')->withTrashed(); }
}
