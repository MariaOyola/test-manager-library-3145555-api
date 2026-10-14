<?php

namespace Library\Adapters\Outbound\Persistence\Models;

final class LoanModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'library.loan';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected function casts(): array { return ['loaned_at' => 'immutable_datetime']; }
    public function member(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(MemberModel::class, 'member_id'); }
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(UserModel::class, 'loaned_by_user_id'); }
    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(LoanItemModel::class, 'loan_id'); }
}
